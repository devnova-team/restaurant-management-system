<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Staff;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

    public function __construct(private OrderRepositoryInterface $orders) {}

    public function list(array $filters, Staff $staff): LengthAwarePaginator
    {
        $filters['per_page'] = min((int) ($filters['per_page'] ?? 15), 100);

        return $this->orders->paginateForRestaurant($staff->restaurant_id, $filters);
    }

    public function show(Order $order, Staff $staff): Order
    {
        return $order->load(['orderItems.menuItem', 'invoice']);
    }

    public function create(Staff $staff, array $data): Order
    {
        return DB::transaction(function () use ($staff, $data) {
            $order = $this->orders->create([
                'restaurant_id'       => $staff->restaurant_id,
                'channel'             => $data['channel'],
                'status'              => 'received',
                'customer_name'       => $data['customer_name'] ?? null,
                'customer_phone'      => $data['customer_phone'] ?? null,
                'delivery_address'    => $data['delivery_address'] ?? null,
                'created_by_staff_id' => $staff->id,
            ]);

            $this->orders->addItems($order, $this->buildItems($order, $data['items']));

            return $this->orders->findForRestaurant($order->id, $staff->restaurant_id);
        });
    }

    public function update(Order $order, array $data, Staff $staff): Order
    {
        $this->assertEditable($order, 'لا يمكن تعديل الطلب بعد بدء تحضيره.');

        return DB::transaction(function () use ($order, $data) {
            $this->orders->update($order, collect($data)->except('items')->all());

            if (isset($data['items'])) {
                $this->orders->deleteItems($order);
                $this->orders->addItems($order, $this->buildItems($order, $data['items']));
            }

            return $order->fresh(['orderItems.menuItem', 'invoice']);
        });
    }

    // public function delete(Order $order, Staff $staff): void
    // {
    //     $this->assertEditable($order, 'لا يمكن حذف الطلب بعد بدء تحضيره.');

    //     // $this->orders->delete($order);
    // }

    public function changeStatus(Order $order, string $newStatus, Staff $staff): Order
    {
        $current = $order->status;

        // 1) هل الانتقال مسموح من الحالة الحالية لنوع الأوردر ده؟
        $allowed = self::TRANSITIONS[$order->channel][$current] ?? [];

        if (! in_array($newStatus, $allowed, true)) {
            $from = self::STATUS_LABELS[$current] ?? $current;
            $to   = self::STATUS_LABELS[$newStatus] ?? $newStatus;

            throw new UnprocessableEntityHttpException(
                "لا يمكن نقل الطلب من حالة ({$from}) إلى حالة ({$to})."
            );
        }

        if (! in_array($staff->role->value, self::ROLES_BY_TARGET[$newStatus] ?? [], true)) {
            throw new AccessDeniedHttpException('ليس لديك صلاحية لتغيير الطلب إلى هذه الحالة.');
        }


        if (! $this->orders->transitionStatus($order, $current, $newStatus)) {
            throw new ConflictHttpException('تم تغيير حالة الطلب من مستخدم آخر، حدّث الصفحة وحاول مرة أخرى.');
        }

        return $this->orders->findForRestaurant($order->id, $staff->restaurant_id);
    }

    private function assertEditable(Order $order, string $message): void
    {
        if ($order->status !== 'received') {
            throw new UnprocessableEntityHttpException($message);
        }
    }

    private function buildItems(Order $order, array $items): array
    {
        $menuItems = MenuItem::query()
            ->whereIn('id', collect($items)->pluck('menu_item_id'))
            ->where('restaurant_id', $order->restaurant_id)
            ->get()
            ->keyBy('id');

        return collect($items)->map(function (array $row) use ($menuItems) {
            $menuItem = $menuItems[$row['menu_item_id']];

            if (! $menuItem->is_available) {
                throw new UnprocessableEntityHttpException("الصنف ({$menuItem->name}) غير متاح حالياً.");
            }

            return [
                'menu_item_id' => $menuItem->id,
                'unit_price'   => $menuItem->price,
                'quantity'     => $row['quantity'],
                'notes'        => $row['notes'] ?? null,
            ];
        })->all();
    }
}
