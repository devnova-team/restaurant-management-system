<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Staff;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class OrderService
{
    private const TRANSITIONS = [
        'dine_in' => [
            'received'  => ['preparing'],
            'preparing' => ['ready'],
            'ready'     => ['served'],
        ],
        'delivery' => [
            'received'         => ['preparing'],
            'preparing'        => ['ready'],
            'ready'            => ['out_for_delivery'],
            'out_for_delivery' => ['delivered'],
        ],
    ];

    private const ROLES_BY_TARGET = [
        'preparing'        => ['kitchen'],
        'ready'            => ['kitchen'],
        'served'           => ['cashier'],
        'out_for_delivery' => ['cashier'],
        'delivered'        => ['cashier'],
    ];

    private const STATUS_LABELS = [
        'received'         => 'تم الاستلام',
        'preparing'        => 'قيد التحضير',
        'ready'            => 'جاهز',
        'served'           => 'تم التقديم',
        'out_for_delivery' => 'خرج للتوصيل',
        'delivered'        => 'تم التسليم',
    ];

    private const KITCHEN_STATUSES = ['received', 'preparing', 'ready'];

    public function __construct(private OrderRepositoryInterface $orders) {}

    public function list(array $filters, Staff $staff): LengthAwarePaginator
    {
        $filters['per_page'] = min((int) ($filters['per_page'] ?? 15), 100);

        return $this->orders->paginateForRestaurant($staff->restaurant_id, $filters);
    }

    public function kitchenOrders(Staff $staff): Collection
    {
        return $this->orders->activeForRestaurant($staff->restaurant_id, self::KITCHEN_STATUSES);
    }

    public function show(Order $order): Order
    {
        return $order->load(['orderItems.menuItem', 'invoice']);
    }

    public function create(Staff $staff, array $data): Order
    {
        return DB::transaction(function () use ($staff, $data) {
            $order = $this->orders->create([
                'restaurant_id'       => $staff->restaurant_id,
                'created_by_staff_id' => $staff->id,
                'channel'             => 'dine_in',
                'status'              => 'received',
            ]);

            $this->orders->addItems($order, $this->formatItems($data['items']));

            $order = $this->orders->findForRestaurant($order->id, $staff->restaurant_id);

            // TODO (Moaz): $this->inventory->deductForOrder($order);

            return $order;
        });
    }

    public function update(Order $order, array $data): Order
    {
        $this->assertPaymentNotRecorded($order);

        return DB::transaction(function () use ($order, $data) {
            // TODO (Moaz): $this->inventory->restoreForOrder($order);  // يرجّع المخزون ويمسح usage rows القديمة

            $this->orders->deleteItems($order);
            $this->orders->addItems($order, $this->formatItems($data['items']));

            $order = $order->fresh(['orderItems.menuItem', 'invoice']);

            // TODO (Moaz): $this->inventory->deductForOrder($order);

            $this->syncUnpaidInvoiceTotal($order);

            return $order;
        });
    }

    public function changeStatus(Order $order, string $newStatus, Staff $staff): Order
    {
        if (! in_array($staff->role->value, self::ROLES_BY_TARGET[$newStatus] ?? [], true)) {
            throw new AccessDeniedHttpException('ليس لديك صلاحية لتغيير الطلب إلى هذه الحالة.');
        }

        $current = $order->status;
        $allowed = self::TRANSITIONS[$order->channel][$current] ?? [];

        if (! in_array($newStatus, $allowed, true)) {
            throw new UnprocessableEntityHttpException($this->transitionError($order, $newStatus));
        }

        if (! $this->orders->transitionStatus($order, $current, $newStatus)) {
            throw new ConflictHttpException('تم تغيير حالة الطلب من مستخدم آخر، حدّث الصفحة وحاول مرة أخرى.');
        }

        return $this->orders->findForRestaurant($order->id, $staff->restaurant_id);
    }

    private function assertPaymentNotRecorded(Order $order): void
    {
        $order->loadMissing('invoice');

        if ($order->invoice?->payment_status === 'paid') {
            throw new UnprocessableEntityHttpException('لا يمكن تعديل الطلب بعد تسجيل دفعة على فاتورته.');
        }
    }

    private function syncUnpaidInvoiceTotal(Order $order): void
    {
        if ($order->invoice?->payment_status === 'unpaid') {
            $this->orders->updateInvoiceTotal($order, $this->calculateTotal($order));
        }
    }

    private function calculateTotal(Order $order): string
    {
        $total = $order->orderItems->sum(
            fn ($item) => $item->quantity * ($item->menuItem?->price ?? 0)
        );

        return number_format($total, 2, '.', '');
    }

    private function transitionError(Order $order, string $newStatus): string
    {
        if ($order->channel === 'dine_in' && in_array($newStatus, ['out_for_delivery', 'delivered'], true)) {
            return 'الطلب Dine-in، القيمة المقبولة served بس.';
        }

        $from = self::STATUS_LABELS[$order->status] ?? $order->status;
        $to   = self::STATUS_LABELS[$newStatus] ?? $newStatus;

        return "لا يمكن نقل الطلب من حالة ({$from}) إلى حالة ({$to}).";
    }

    private function formatItems(array $items): array
    {
        return array_map(fn (array $item) => [
            'menu_item_id' => $item['menu_item_id'],
            'quantity'     => $item['quantity'],
            'notes'        => $item['notes'] ?? null,
        ], $items);
    }
}
