import { Head, useProp } from '@inertiajs/react';
import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from 'recharts';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    ChartContainer,
    ChartTooltip,
    ChartTooltipContent,
} from '@/components/ui/chart';
import type { ChartConfig } from '@/components/ui/chart';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    formatDateTime,
    formatHour,
    formatMoney,
    formatOrderNumber,
} from '@/lib/format';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes';
import type { OrdersOverview } from '@/types';

type DashboardProps = {
    ordersOverview: OrdersOverview;
    newUsers: number;
};

const chartConfig = {
    count: {
        label: 'Orders',
        color: 'var(--chart-1)',
    },
} satisfies ChartConfig;

export default function Dashboard({
    ordersOverview,
    newUsers,
}: DashboardProps) {
    const { timezone, currency } = ordersOverview;
    const isRefreshingOrders = useProp('ordersOverview').loading;

    const chartData = ordersOverview.hourly.map((bucket) => ({
        ...bucket,
        label: formatHour(bucket.hour, timezone),
    }));

    return (
        <>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-baseline justify-between gap-2">
                    <div className="flex items-center gap-3">
                        <h1 className="text-xl font-semibold tracking-tight">
                            Orders
                        </h1>
                        <p
                            role="status"
                            aria-live="polite"
                            className="flex items-center gap-1.5 text-sm text-muted-foreground"
                        >
                            {isRefreshingOrders && (
                                <>
                                    <Spinner className="size-3.5" aria-hidden="true" />
                                    Updating orders…
                                </>
                            )}
                        </p>
                    </div>
                    <p className="text-sm text-muted-foreground">
                        Last 24 hours ({timezone})
                    </p>
                </div>

                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <MetricCard
                        title="Orders"
                        value={ordersOverview.orderCount.toLocaleString()}
                        description={`Last 24 hourly buckets (${timezone})`}
                    />
                    <MetricCard
                        title="Order value"
                        value={formatMoney(
                            ordersOverview.orderValueCents,
                            currency,
                        )}
                        description={`Sum of order totals (${currency})`}
                    />
                    <MetricCard
                        title="New users"
                        value={newUsers.toLocaleString()}
                        description="Registrations in the last 24 hours"
                    />
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Orders by hour</CardTitle>
                        <CardDescription>
                            Hourly order count, times in {timezone}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <ChartContainer
                            config={chartConfig}
                            className="aspect-auto h-64 w-full"
                        >
                            <BarChart
                                accessibilityLayer
                                data={chartData}
                                margin={{ left: -20, right: 4 }}
                            >
                                <CartesianGrid vertical={false} />
                                <XAxis
                                    dataKey="label"
                                    tickLine={false}
                                    axisLine={false}
                                    tickMargin={8}
                                    minTickGap={16}
                                />
                                <YAxis
                                    allowDecimals={false}
                                    tickLine={false}
                                    axisLine={false}
                                    width={48}
                                />
                                <ChartTooltip
                                    cursor={false}
                                    content={<ChartTooltipContent />}
                                />
                                <Bar
                                    dataKey="count"
                                    fill="var(--color-count)"
                                    radius={4}
                                    isAnimationActive={false}
                                />
                            </BarChart>
                        </ChartContainer>
                    </CardContent>
                </Card>

                <Card id="latest-orders" className="scroll-mt-4">
                    <CardHeader>
                        <CardTitle>Latest 15 orders</CardTitle>
                        <CardDescription>
                            Newest first, times in {timezone}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Order</TableHead>
                                    <TableHead className="text-right">
                                        Total
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Created
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {ordersOverview.latestOrders.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={3}
                                            className="h-24 text-center text-muted-foreground"
                                        >
                                            No orders yet.
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    ordersOverview.latestOrders.map((order) => (
                                        <TableRow key={order.id}>
                                            <TableCell className="font-medium tabular-nums">
                                                {formatOrderNumber(order.id)}
                                            </TableCell>
                                            <TableCell className="text-right tabular-nums">
                                                {formatMoney(
                                                    order.totalPriceCents,
                                                    currency,
                                                )}
                                            </TableCell>
                                            <TableCell className="text-right text-muted-foreground tabular-nums">
                                                <time
                                                    dateTime={order.createdAt}
                                                >
                                                    {formatDateTime(
                                                        order.createdAt,
                                                        timezone,
                                                    )}
                                                </time>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

function MetricCard({
    title,
    value,
    description,
}: {
    title: string;
    value: string;
    description: string;
}) {
    return (
        <Card className="gap-2">
            <CardHeader>
                <CardDescription>{title}</CardDescription>
                <CardTitle className="text-3xl font-semibold tabular-nums">
                    {value}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <p className="text-xs text-muted-foreground">{description}</p>
            </CardContent>
        </Card>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
