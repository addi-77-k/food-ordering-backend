import AdminLayout from '@/layouts/admin-layout';
import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowRight,
    ArrowUpRight,
    CheckCircle2,
    ChefHat,
    Clock,
    CreditCard,
    DollarSign,
    Layers,
    PackageCheck,
    Plus,
    ShoppingBag,
    Sparkles,
    Truck,
    Users,
    Utensils,
    XCircle,
} from 'lucide-react';

interface Statistics {
    total_orders: number;
    pending_orders: number;
    confirmed_orders: number;
    preparing_orders: number;
    ready_orders: number;
    out_for_delivery_orders: number;
    completed_orders: number;
    cancelled_orders: number;
    total_customers: number;
    total_menu_items: number;
    total_categories: number;
    total_revenue: number;
}

interface OrderItem {
    name: string;
    quantity: number;
    unit_price: number;
    subtotal: number;
}

interface RecentOrder {
    id: string;
    order_number: string;
    customer?: {
        name: string;
        email: string;
    };
    items?: OrderItem[];
    subtotal: number;
    delivery_fee: number;
    discount: number;
    total: number;
    payment_method: string;
    payment_status: string;
    order_status: string;
    created_at: string;
}

interface SalesSummary {
    kpis: Statistics;
    payment_summary: {
        paid_orders_count: number;
        pending_payment_count: number;
        cash_on_delivery_count: number;
        online_payment_count: number;
    };
    status_distribution: Record<string, number>;
}

interface DashboardProps {
    statistics: Statistics;
    recentOrders: RecentOrder[];
    salesSummary: SalesSummary;
}

export default function Dashboard({ statistics, recentOrders, salesSummary }: DashboardProps) {
    const activeOrdersCount =
        statistics.pending_orders +
        statistics.confirmed_orders +
        statistics.preparing_orders +
        statistics.ready_orders +
        statistics.out_for_delivery_orders;

    const pipelineSteps = [
        {
            key: 'pending',
            label: 'Pending',
            count: statistics.pending_orders,
            color: 'from-amber-500/20 to-amber-500/5 border-amber-500/30 text-amber-400',
            icon: Clock,
        },
        {
            key: 'confirmed',
            label: 'Confirmed',
            count: statistics.confirmed_orders,
            color: 'from-blue-500/20 to-blue-500/5 border-blue-500/30 text-blue-400',
            icon: CheckCircle2,
        },
        {
            key: 'preparing',
            label: 'In Kitchen',
            count: statistics.preparing_orders,
            color: 'from-orange-500/20 to-orange-500/5 border-orange-500/30 text-orange-400',
            icon: ChefHat,
        },
        {
            key: 'ready',
            label: 'Ready',
            count: statistics.ready_orders,
            color: 'from-purple-500/20 to-purple-500/5 border-purple-500/30 text-purple-400',
            icon: Utensils,
        },
        {
            key: 'out_for_delivery',
            label: 'Dispatching',
            count: statistics.out_for_delivery_orders,
            color: 'from-cyan-500/20 to-cyan-500/5 border-cyan-500/30 text-cyan-400',
            icon: Truck,
        },
        {
            key: 'completed',
            label: 'Delivered',
            count: statistics.completed_orders,
            color: 'from-emerald-500/20 to-emerald-500/5 border-emerald-500/30 text-emerald-400',
            icon: PackageCheck,
        },
        {
            key: 'cancelled',
            label: 'Cancelled',
            count: statistics.cancelled_orders,
            color: 'from-rose-500/20 to-rose-500/5 border-rose-500/30 text-rose-400',
            icon: XCircle,
        },
    ];

    const getStatusBadge = (status: string) => {
        const map: Record<string, { bg: string; text: string }> = {
            pending: { bg: 'bg-amber-500/10 border-amber-500/20', text: 'text-amber-400' },
            confirmed: { bg: 'bg-blue-500/10 border-blue-500/20', text: 'text-blue-400' },
            preparing: { bg: 'bg-orange-500/10 border-orange-500/20', text: 'text-orange-400' },
            ready: { bg: 'bg-purple-500/10 border-purple-500/20', text: 'text-purple-400' },
            out_for_delivery: { bg: 'bg-cyan-500/10 border-cyan-500/20', text: 'text-cyan-400' },
            completed: { bg: 'bg-emerald-500/10 border-emerald-500/20', text: 'text-emerald-400' },
            cancelled: { bg: 'bg-rose-500/10 border-rose-500/20', text: 'text-rose-400' },
        };
        const s = map[status] || { bg: 'bg-neutral-800 border-neutral-700', text: 'text-neutral-300' };
        return (
            <span className={`inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium border ${s.bg} ${s.text}`}>
                <span className="h-1.5 w-1.5 rounded-full bg-current" />
                {status.replace(/_/g, ' ')}
            </span>
        );
    };

    return (
        <AdminLayout title="Admin Overview">
            <Head title="Admin Dashboard - FoodieOS" />

            <div className="space-y-8">
                {/* Header Banner */}
                <div className="relative overflow-hidden rounded-2xl border border-neutral-800 bg-gradient-to-r from-neutral-900 via-neutral-900/90 to-neutral-950 p-6 sm:p-8">
                    <div className="absolute right-0 top-0 h-full w-1/3 bg-gradient-to-l from-orange-500/10 to-transparent pointer-events-none" />
                    <div className="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <div className="inline-flex items-center gap-2 rounded-full border border-orange-500/30 bg-orange-500/10 px-3 py-1 text-xs font-semibold text-orange-400 mb-2">
                                <Sparkles className="h-3.5 w-3.5" />
                                Real-Time Operations Control
                            </div>
                            <h1 className="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                                Food Ordering Console
                            </h1>
                            <p className="text-sm text-neutral-400 mt-1 max-w-xl">
                                Live monitoring of orders, kitchen dispatch, menu inventory, and platform revenue powered by MongoDB.
                            </p>
                        </div>

                        <div className="flex items-center gap-3">
                            <Link
                                href="/admin/orders"
                                className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 hover:from-orange-600 hover:to-amber-600 transition"
                            >
                                <ShoppingBag className="h-4 w-4" />
                                Manage Live Orders
                            </Link>
                        </div>
                    </div>
                </div>

                {/* Primary Metric KPI Cards */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {/* Revenue Card */}
                    <div className="rounded-2xl border border-neutral-800 bg-neutral-900/60 p-5 shadow-sm hover:border-neutral-700 transition">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-medium text-neutral-400">Total Net Revenue</span>
                            <div className="rounded-xl bg-emerald-500/10 p-2 text-emerald-400 border border-emerald-500/20">
                                <DollarSign className="h-5 w-5" />
                            </div>
                        </div>
                        <div className="mt-4">
                            <div className="text-3xl font-extrabold text-white">
                                ₱{Number(statistics.total_revenue).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                            </div>
                            <div className="mt-1 flex items-center gap-1.5 text-xs text-emerald-400">
                                <CheckCircle2 className="h-3.5 w-3.5" />
                                <span>From {statistics.completed_orders} completed orders</span>
                            </div>
                        </div>
                    </div>

                    {/* Active In-Flight Orders */}
                    <div className="rounded-2xl border border-neutral-800 bg-neutral-900/60 p-5 shadow-sm hover:border-neutral-700 transition">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-medium text-neutral-400">Active In-Flight Orders</span>
                            <div className="rounded-xl bg-amber-500/10 p-2 text-amber-400 border border-amber-500/20">
                                <Clock className="h-5 w-5" />
                            </div>
                        </div>
                        <div className="mt-4">
                            <div className="text-3xl font-extrabold text-white">{activeOrdersCount}</div>
                            <div className="mt-1 flex items-center gap-1.5 text-xs text-amber-400">
                                <span className="h-2 w-2 rounded-full bg-amber-400 animate-ping" />
                                <span>{statistics.pending_orders} awaiting confirmation</span>
                            </div>
                        </div>
                    </div>

                    {/* Registered Customers */}
                    <div className="rounded-2xl border border-neutral-800 bg-neutral-900/60 p-5 shadow-sm hover:border-neutral-700 transition">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-medium text-neutral-400">Total Foodies</span>
                            <div className="rounded-xl bg-blue-500/10 p-2 text-blue-400 border border-blue-500/20">
                                <Users className="h-5 w-5" />
                            </div>
                        </div>
                        <div className="mt-4">
                            <div className="text-3xl font-extrabold text-white">{statistics.total_customers}</div>
                            <div className="mt-1 text-xs text-neutral-400">
                                Across active customer profiles
                            </div>
                        </div>
                    </div>

                    {/* Total Menu Catalog */}
                    <div className="rounded-2xl border border-neutral-800 bg-neutral-900/60 p-5 shadow-sm hover:border-neutral-700 transition">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-medium text-neutral-400">Menu Catalog</span>
                            <div className="rounded-xl bg-orange-500/10 p-2 text-orange-400 border border-orange-500/20">
                                <ChefHat className="h-5 w-5" />
                            </div>
                        </div>
                        <div className="mt-4">
                            <div className="text-3xl font-extrabold text-white">{statistics.total_menu_items}</div>
                            <div className="mt-1 text-xs text-neutral-400">
                                Across {statistics.total_categories} gourmet categories
                            </div>
                        </div>
                    </div>
                </div>

                {/* Lifecycle Pipeline Funnel */}
                <div className="space-y-3">
                    <div className="flex items-center justify-between">
                        <h2 className="text-base font-bold text-white flex items-center gap-2">
                            <span>Order Lifecycle Flow</span>
                            <span className="text-xs font-normal text-neutral-400">Click any step to filter orders</span>
                        </h2>
                        <Link href="/admin/orders" className="text-xs font-medium text-orange-400 hover:text-orange-300 flex items-center gap-1">
                            View all {statistics.total_orders} orders <ArrowRight className="h-3 w-3" />
                        </Link>
                    </div>

                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
                        {pipelineSteps.map((step) => {
                            const Icon = step.icon;
                            return (
                                <Link
                                    key={step.key}
                                    href={`/admin/orders?status=${step.key}`}
                                    className={`group rounded-xl border bg-gradient-to-b p-3.5 transition-all hover:scale-[1.02] ${step.color}`}
                                >
                                    <div className="flex items-center justify-between">
                                        <span className="text-xs font-medium opacity-80">{step.label}</span>
                                        <Icon className="h-4 w-4 opacity-70 group-hover:opacity-100 transition-opacity" />
                                    </div>
                                    <div className="mt-2 text-2xl font-black text-white">{step.count}</div>
                                </Link>
                            );
                        })}
                    </div>
                </div>

                {/* Split Section: Recent Live Orders & Payment Breakdown */}
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* Live Orders Feed (2 cols) */}
                    <div className="lg:col-span-2 rounded-2xl border border-neutral-800 bg-neutral-900/60 p-6">
                        <div className="flex items-center justify-between mb-4">
                            <div>
                                <h3 className="text-base font-bold text-white">Recent Orders Feed</h3>
                                <p className="text-xs text-neutral-400">Latest orders placed on the platform</p>
                            </div>
                            <Link
                                href="/admin/orders"
                                className="text-xs font-medium text-orange-400 hover:text-orange-300 flex items-center gap-1"
                            >
                                Open Orders Table <ArrowUpRight className="h-3 w-3" />
                            </Link>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-xs">
                                <thead>
                                    <tr className="border-b border-neutral-800 text-neutral-400 font-medium">
                                        <th className="pb-3 font-semibold">Order</th>
                                        <th className="pb-3 font-semibold">Customer</th>
                                        <th className="pb-3 font-semibold">Items</th>
                                        <th className="pb-3 font-semibold">Total</th>
                                        <th className="pb-3 font-semibold">Status</th>
                                        <th className="pb-3 font-semibold text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-800/60">
                                    {recentOrders.length === 0 ? (
                                        <tr>
                                            <td colSpan={6} className="py-8 text-center text-neutral-500">
                                                No orders placed yet.
                                            </td>
                                        </tr>
                                    ) : (
                                        recentOrders.map((order) => (
                                            <tr key={order.id} className="hover:bg-neutral-800/30 transition">
                                                <td className="py-3 font-mono font-bold text-white">
                                                    {order.order_number}
                                                </td>
                                                <td className="py-3 text-neutral-200">
                                                    {order.customer?.name || 'Walk-in Guest'}
                                                </td>
                                                <td className="py-3 text-neutral-400">
                                                    {order.items?.length ?? 0} items
                                                </td>
                                                <td className="py-3 font-semibold text-white">
                                                    ₱{Number(order.total).toFixed(2)}
                                                </td>
                                                <td className="py-3">
                                                    {getStatusBadge(order.order_status)}
                                                </td>
                                                <td className="py-3 text-right">
                                                    <Link
                                                        href={`/admin/orders?search=${order.order_number}`}
                                                        className="inline-flex items-center gap-1 rounded-lg bg-neutral-800 px-2.5 py-1 text-xs font-medium text-neutral-300 hover:bg-neutral-700 hover:text-white transition"
                                                    >
                                                        Details
                                                    </Link>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* Payment & Operations Summary (1 col) */}
                    <div className="space-y-6">
                        {/* Payment Breakdown Card */}
                        <div className="rounded-2xl border border-neutral-800 bg-neutral-900/60 p-6">
                            <h3 className="text-base font-bold text-white flex items-center gap-2">
                                <CreditCard className="h-4 w-4 text-orange-400" />
                                Payment Methods
                            </h3>
                            <p className="text-xs text-neutral-400 mt-0.5">Transaction split by payment type</p>

                            <div className="mt-4 space-y-3">
                                <div className="flex items-center justify-between rounded-xl bg-neutral-800/40 p-3">
                                    <div className="flex items-center gap-2.5">
                                        <div className="rounded-lg bg-emerald-500/10 p-2 text-emerald-400">
                                            <CreditCard className="h-4 w-4" />
                                        </div>
                                        <div>
                                            <div className="text-xs font-semibold text-white">Online / Card</div>
                                            <div className="text-[10px] text-neutral-400">Instant gateway checkout</div>
                                        </div>
                                    </div>
                                    <span className="text-base font-bold text-white">
                                        {salesSummary.payment_summary.online_payment_count}
                                    </span>
                                </div>

                                <div className="flex items-center justify-between rounded-xl bg-neutral-800/40 p-3">
                                    <div className="flex items-center gap-2.5">
                                        <div className="rounded-lg bg-amber-500/10 p-2 text-amber-400">
                                            <DollarSign className="h-4 w-4" />
                                        </div>
                                        <div>
                                            <div className="text-xs font-semibold text-white">Cash on Delivery</div>
                                            <div className="text-[10px] text-neutral-400">Paid upon courier delivery</div>
                                        </div>
                                    </div>
                                    <span className="text-base font-bold text-white">
                                        {salesSummary.payment_summary.cash_on_delivery_count}
                                    </span>
                                </div>
                            </div>

                            <div className="mt-4 border-t border-neutral-800 pt-3 flex items-center justify-between text-xs text-neutral-400">
                                <span>Settled Paid Orders</span>
                                <span className="font-semibold text-emerald-400">
                                    {salesSummary.payment_summary.paid_orders_count} orders
                                </span>
                            </div>
                        </div>

                        {/* Quick Links Card */}
                        <div className="rounded-2xl border border-neutral-800 bg-neutral-900/60 p-6">
                            <h3 className="text-base font-bold text-white mb-3">Quick Navigation</h3>
                            <div className="space-y-2">
                                <Link
                                    href="/admin/menu-items"
                                    className="flex items-center justify-between rounded-xl border border-neutral-800 bg-neutral-800/40 p-3 text-xs font-medium text-neutral-200 hover:bg-neutral-800 hover:border-neutral-700 transition"
                                >
                                    <div className="flex items-center gap-2.5">
                                        <ChefHat className="h-4 w-4 text-orange-400" />
                                        <span>Manage Menu Items</span>
                                    </div>
                                    <ArrowRight className="h-3.5 w-3.5 text-neutral-500" />
                                </Link>

                                <Link
                                    href="/admin/categories"
                                    className="flex items-center justify-between rounded-xl border border-neutral-800 bg-neutral-800/40 p-3 text-xs font-medium text-neutral-200 hover:bg-neutral-800 hover:border-neutral-700 transition"
                                >
                                    <div className="flex items-center gap-2.5">
                                        <Layers className="h-4 w-4 text-amber-400" />
                                        <span>Categories Manager</span>
                                    </div>
                                    <ArrowRight className="h-3.5 w-3.5 text-neutral-500" />
                                </Link>

                                <Link
                                    href="/admin/customers"
                                    className="flex items-center justify-between rounded-xl border border-neutral-800 bg-neutral-800/40 p-3 text-xs font-medium text-neutral-200 hover:bg-neutral-800 hover:border-neutral-700 transition"
                                >
                                    <div className="flex items-center gap-2.5">
                                        <Users className="h-4 w-4 text-blue-400" />
                                        <span>Customers Directory</span>
                                    </div>
                                    <ArrowRight className="h-3.5 w-3.5 text-neutral-500" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
