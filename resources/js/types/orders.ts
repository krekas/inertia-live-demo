export type HourlyOrderBucket = {
    hour: string;
    count: number;
};

export type RecentOrder = {
    id: number;
    totalPriceCents: number;
    createdAt: string;
};

export type OrdersOverview = {
    timezone: string;
    currency: string;
    windowStart: string;
    orderCount: number;
    orderValueCents: number;
    hourly: HourlyOrderBucket[];
    latestOrders: RecentOrder[];
};
