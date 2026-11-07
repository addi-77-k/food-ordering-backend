import AdminLayout from '@/layouts/admin-layout';
import { Head, router } from '@inertiajs/react';
import {
    AlertCircle,
    ArrowRight,
    Ban,
    Calendar,
    CheckCircle2,
    Clock,
    CreditCard,
    DollarSign,
    Eye,
    Filter,
    MapPin,
    Phone,
    RefreshCw,
    Search,
    Trash2,
    Truck,
    User,
    Utensils,
    X,
} from 'lucide-react';
import React, { useState } from 'react';

interface OrderItem {
    menu_item_id?: string;
    name: string;
    quantity: number;
    unit_price: number;
    subtotal: number;
}

interface StatusHistoryItem {
    from?: string | null;
    status: string;
    comment?: string;
    created_at: string;
    actor?: string;
}

interface Order {
    id: string;
    order_number: string;
    customer_id?: string;
    customer?: {
        id: string;
        name: string;
        email: string;
        phone?: string;
    };
    items: OrderItem[];
    subtotal: number;
    delivery_fee: number;
    discount: number;
    total: number;
    delivery_address: string;
    payment_method: string;
    payment_status: string;
    order_status: string;
    notes?: string;
    status_history?: StatusHistoryItem[];
    created_at: string;
}

interface OrdersIndexProps {
    orders: {
        data: Order[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        status: string;
        payment_status: string;
        search: string;
    };
}

export default function OrdersIndex({ orders, filters }: OrdersIndexProps) {
    const [selectedOrder, setSelectedOrder] = useState<Order | null>(null);
    const [cancelModalOrder, setCancelModalOrder] = useState<Order | null>(null);
    const [cancelReason, setCancelReason] = useState('');
    const [statusComment, setStatusComment] = useState('');
    const [search, setSearch] = useState(filters.search || '');

    const handleFilterChange = (key: string, value: string) => {
        router.get(
            '/admin/orders',
            { ...filters, [key]: value, page: 1 },
            { preserveState: true, replace: true }
        );
    };

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        handleFilterChange('search', search);
    };

    const advanceStatus = (orderId: string, targetStatus: string, comment?: string) => {
        router.patch(
            `/admin/orders/${orderId}/status`,
            { status: targetStatus, comment: comment || `Status advanced to ${targetStatus}` },
            {
                onSuccess: () => {
                    if (selectedOrder && selectedOrder.id === orderId) {
                        setSelectedOrder(null);
                    }
                },
            }
        );
    };

    const handleCancelOrder = (e: React.FormEvent) => {
        e.preventDefault();
        if (!cancelModalOrder) return;

        router.post(
            `/admin/orders/${cancelModalOrder.id}/cancel`,
            { reason: cancelReason || 'Order cancelled by administrator' },
            {
                onSuccess: () => {
                    setCancelModalOrder(null);
                    setCancelReason('');
                    setSelectedOrder(null);
                },
            }
        );
    };

    const handleDeleteOrder = (orderId: string, orderNumber: string) => {
        if (confirm(`Are you sure you want to permanently delete Order #${orderNumber}?`)) {
            router.delete(`/admin/orders/${orderId}`);
        }
    };

    // State machine allowed transitions
    const getAllowedTransitions = (currentStatus: string): string[] => {
        const transitions: Record<string, string[]> = {
            pending: ['confirmed', 'cancelled'],
            confirmed: ['preparing', 'cancelled'],
            preparing: ['ready', 'cancelled'],
            ready: ['out_for_delivery'],
            out_for_delivery: ['completed'],
            completed: [],
            cancelled: [],
        };
        return transitions[currentStatus] || [];
    };

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
            <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border ${s.bg} ${s.text}`}>
                <span className="h-1.5 w-1.5 rounded-full bg-current" />
                {status.replace(/_/g, ' ')}
            </span>
        );
    };

    const statusTabs = [
        { key: 'all', label: 'All Orders' },
        { key: 'pending', label: 'Pending' },
        { key: 'confirmed', label: 'Confirmed' },
        { key: 'preparing', label: 'In Kitchen' },
        { key: 'ready', label: 'Ready' },
        { key: 'out_for_delivery', label: 'Out for Delivery' },
        { key: 'completed', label: 'Completed' },
        { key: 'cancelled', label: 'Cancelled' },
    ];

    return (
        <AdminLayout title="Orders Management">
            <Head title="Orders Management - FoodieOS Admin" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-black tracking-tight text-white flex items-center gap-2.5">
                            Orders Management
                            <span className="rounded-lg bg-orange-500/10 px-2.5 py-0.5 text-xs font-bold text-orange-400 border border-orange-500/20">
                                {orders.total} Total
                            </span>
                        </h1>
                        <p className="text-xs text-neutral-400 mt-1">
                            Track, dispatch, advance lifecycle states, and inspect itemized customer receipts.
                        </p>
                    </div>
                </div>

                {/* Filter & Search Bar */}
                <div className="rounded-2xl border border-neutral-800 bg-neutral-900/60 p-4 space-y-4">
                    {/* Status Tabs */}
                    <div className="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-thin">
                        {statusTabs.map((tab) => (
                            <button
                                key={tab.key}
                                type="button"
                                onClick={() => handleFilterChange('status', tab.key)}
                                className={`rounded-xl px-3.5 py-1.5 text-xs font-semibold whitespace-nowrap transition ${
                                    filters.status === tab.key
                                        ? 'bg-orange-500 text-white shadow-md shadow-orange-500/20'
                                        : 'bg-neutral-800/60 text-neutral-400 hover:bg-neutral-800 hover:text-white'
                                }`}
                            >
                                {tab.label}
                            </button>
                        ))}
                    </div>

                    {/* Search & Secondary Filters */}
                    <div className="flex flex-col sm:flex-row items-center gap-3">
                        <form onSubmit={handleSearchSubmit} className="relative flex-1 w-full">
                            <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-500" />
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search by Order # (e.g. ORD-2026) or address..."
                                className="w-full rounded-xl border border-neutral-800 bg-neutral-950/80 pl-10 pr-4 py-2 text-xs text-neutral-200 placeholder-neutral-500 focus:border-orange-500 focus:outline-none focus:ring-1 focus:ring-orange-500"
                            />
                        </form>

                        <div className="flex items-center gap-2 w-full sm:w-auto">
                            <select
                                value={filters.payment_status || 'all'}
                                onChange={(e) => handleFilterChange('payment_status', e.target.value)}
                                className="rounded-xl border border-neutral-800 bg-neutral-950/80 px-3 py-2 text-xs text-neutral-300 focus:border-orange-500 focus:outline-none"
                            >
                                <option value="all">All Payments</option>
                                <option value="paid">Paid</option>
                                <option value="pending">Pending Payment</option>
                            </select>
                        </div>
                    </div>
                </div>

                {/* Orders Table */}
                <div className="rounded-2xl border border-neutral-800 bg-neutral-900/60 overflow-hidden shadow-sm">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="border-b border-neutral-800 bg-neutral-900/90 text-neutral-400 font-semibold uppercase tracking-wider text-[11px]">
                                <tr>
                                    <th className="px-5 py-3.5">Order</th>
                                    <th className="px-5 py-3.5">Customer</th>
                                    <th className="px-5 py-3.5">Dishes</th>
                                    <th className="px-5 py-3.5">Total</th>
                                    <th className="px-5 py-3.5">Payment</th>
                                    <th className="px-5 py-3.5">Status</th>
                                    <th className="px-5 py-3.5">Date</th>
                                    <th className="px-5 py-3.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-neutral-800/60">
                                {orders.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={8} className="py-12 text-center text-neutral-500">
                                            No matching orders found.
                                        </td>
                                    </tr>
                                ) : (
                                    orders.data.map((order) => {
                                        const allowed = getAllowedTransitions(order.order_status);
                                        const nextAction = allowed.find((s) => s !== 'cancelled');

                                        return (
                                            <tr key={order.id} className="hover:bg-neutral-800/30 transition group">
                                                <td className="px-5 py-4 font-mono font-bold text-white">
                                                    {order.order_number}
                                                </td>
                                                <td className="px-5 py-4">
                                                    <div className="font-semibold text-neutral-200">
                                                        {order.customer?.name || 'Guest'}
                                                    </div>
                                                    <div className="text-[11px] text-neutral-500 truncate max-w-[160px]">
                                                        {order.delivery_address}
                                                    </div>
                                                </td>
                                                <td className="px-5 py-4 text-neutral-300">
                                                    <span className="font-medium text-white">
                                                        {order.items?.length || 0} items
                                                    </span>
                                                    <div className="text-[11px] text-neutral-500 truncate max-w-[180px]">
                                                        {order.items?.map((i) => `${i.quantity}x ${i.name}`).join(', ') || 'No line items'}
                                                    </div>
                                                </td>
                                                <td className="px-5 py-4 font-bold text-white text-sm">
                                                    ₱{Number(order.total).toFixed(2)}
                                                </td>
                                                <td className="px-5 py-4">
                                                    <span className={`inline-flex px-2 py-0.5 rounded text-[10px] font-semibold uppercase ${
                                                        order.payment_status === 'paid'
                                                            ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                                                            : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'
                                                    }`}>
                                                        {order.payment_status}
                                                    </span>
                                                    <div className="text-[10px] text-neutral-500 capitalize mt-0.5">
                                                        {order.payment_method?.replace(/_/g, ' ')}
                                                    </div>
                                                </td>
                                                <td className="px-5 py-4">
                                                    {getStatusBadge(order.order_status)}
                                                </td>
                                                <td className="px-5 py-4 text-neutral-400 whitespace-nowrap text-[11px]">
                                                    {new Date(order.created_at).toLocaleDateString()}
                                                </td>
                                                <td className="px-5 py-4 text-right whitespace-nowrap space-x-1.5">
                                                    <button
                                                        type="button"
                                                        onClick={() => setSelectedOrder(order)}
                                                        className="inline-flex items-center gap-1 rounded-lg bg-neutral-800 px-2.5 py-1.5 text-xs font-semibold text-neutral-200 hover:bg-neutral-700 hover:text-white transition"
                                                        title="View Details"
                                                    >
                                                        <Eye className="h-3.5 w-3.5" />
                                                        Details
                                                    </button>

                                                    {nextAction && (
                                                        <button
                                                            type="button"
                                                            onClick={() => advanceStatus(order.id, nextAction)}
                                                            className="inline-flex items-center gap-1 rounded-lg bg-orange-500 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-orange-600 transition shadow-sm shadow-orange-500/20"
                                                            title={`Advance to ${nextAction}`}
                                                        >
                                                            {nextAction === 'confirmed' && 'Confirm'}
                                                            {nextAction === 'preparing' && 'Kitchen'}
                                                            {nextAction === 'ready' && 'Ready'}
                                                            {nextAction === 'out_for_delivery' && 'Dispatch'}
                                                            {nextAction === 'completed' && 'Complete'}
                                                        </button>
                                                    )}

                                                    {order.order_status !== 'completed' &&
                                                        order.order_status !== 'cancelled' &&
                                                        order.order_status !== 'ready' &&
                                                        order.order_status !== 'out_for_delivery' && (
                                                            <button
                                                                type="button"
                                                                onClick={() => setCancelModalOrder(order)}
                                                                className="inline-flex items-center rounded-lg p-1.5 text-rose-400 hover:bg-rose-500/10 transition"
                                                                title="Cancel Order"
                                                            >
                                                                <Ban className="h-3.5 w-3.5" />
                                                            </button>
                                                        )}

                                                    <button
                                                        type="button"
                                                        onClick={() => handleDeleteOrder(order.id, order.order_number)}
                                                        className="inline-flex items-center rounded-lg p-1.5 text-neutral-500 hover:text-rose-400 hover:bg-rose-500/10 transition"
                                                        title="Delete Order"
                                                    >
                                                        <Trash2 className="h-3.5 w-3.5" />
                                                    </button>
                                                </td>
                                            </tr>
                                        );
                                    })
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {orders.last_page > 1 && (
                        <div className="flex items-center justify-between border-t border-neutral-800 px-5 py-3 text-xs text-neutral-400">
                            <div>
                                Showing Page {orders.current_page} of {orders.last_page} ({orders.total} orders)
                            </div>
                            <div className="flex items-center gap-1.5">
                                {orders.current_page > 1 && (
                                    <button
                                        type="button"
                                        onClick={() => handleFilterChange('page', String(orders.current_page - 1))}
                                        className="rounded-lg border border-neutral-800 px-3 py-1.5 text-neutral-200 hover:bg-neutral-800"
                                    >
                                        Previous
                                    </button>
                                )}
                                {orders.current_page < orders.last_page && (
                                    <button
                                        type="button"
                                        onClick={() => handleFilterChange('page', String(orders.current_page + 1))}
                                        className="rounded-lg border border-neutral-800 px-3 py-1.5 text-neutral-200 hover:bg-neutral-800"
                                    >
                                        Next
                                    </button>
                                )}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Order Details Modal */}
            {selectedOrder && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in">
                    <div className="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl border border-neutral-800 bg-neutral-900 p-6 shadow-2xl">
                        <div className="flex items-center justify-between border-b border-neutral-800 pb-4">
                            <div>
                                <div className="text-xs font-semibold text-orange-400 uppercase tracking-wider">
                                    Receipt & Audit Log
                                </div>
                                <h3 className="text-xl font-extrabold text-white flex items-center gap-2 mt-0.5">
                                    Order #{selectedOrder.order_number}
                                    {getStatusBadge(selectedOrder.order_status)}
                                </h3>
                            </div>
                            <button
                                type="button"
                                onClick={() => setSelectedOrder(null)}
                                className="rounded-lg p-1.5 text-neutral-400 hover:bg-neutral-800 hover:text-white"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        <div className="mt-5 space-y-6 text-xs">
                            {/* Customer & Delivery Details */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 rounded-xl border border-neutral-800/80 bg-neutral-950/60 p-4">
                                <div>
                                    <div className="font-semibold text-neutral-400 mb-1 flex items-center gap-1.5">
                                        <User className="h-3.5 w-3.5 text-orange-400" /> Customer Information
                                    </div>
                                    <div className="text-neutral-200 font-bold text-sm">
                                        {selectedOrder.customer?.name || 'Guest'}
                                    </div>
                                    <div className="text-neutral-400">{selectedOrder.customer?.email}</div>
                                    {selectedOrder.customer?.phone && (
                                        <div className="text-neutral-400 flex items-center gap-1 mt-0.5">
                                            <Phone className="h-3 w-3" /> {selectedOrder.customer.phone}
                                        </div>
                                    )}
                                </div>

                                <div>
                                    <div className="font-semibold text-neutral-400 mb-1 flex items-center gap-1.5">
                                        <MapPin className="h-3.5 w-3.5 text-orange-400" /> Delivery Address
                                    </div>
                                    <div className="text-neutral-200">{selectedOrder.delivery_address}</div>
                                    {selectedOrder.notes && (
                                        <div className="mt-1 text-neutral-400 italic">
                                            Note: "{selectedOrder.notes}"
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Itemized Snapshot Breakdown */}
                            <div>
                                <h4 className="font-bold text-white mb-2 flex items-center gap-1.5">
                                    <Utensils className="h-4 w-4 text-orange-400" />
                                    Snapshotted Order Items
                                </h4>
                                <div className="rounded-xl border border-neutral-800 bg-neutral-950/60 divide-y divide-neutral-800/80">
                                    {selectedOrder.items?.map((item, idx) => (
                                        <div key={idx} className="flex items-center justify-between p-3">
                                            <div className="flex items-center gap-3">
                                                <span className="flex h-6 w-6 items-center justify-center rounded-lg bg-orange-500/10 text-orange-400 font-bold text-xs">
                                                    {item.quantity}x
                                                </span>
                                                <div>
                                                    <div className="font-semibold text-neutral-200">{item.name}</div>
                                                    <div className="text-[11px] text-neutral-400">
                                                        ₱{Number(item.unit_price).toFixed(2)} each
                                                    </div>
                                                </div>
                                            </div>
                                            <span className="font-bold text-white">
                                                ₱{Number(item.subtotal).toFixed(2)}
                                            </span>
                                        </div>
                                    ))}
                                    <div className="p-3 bg-neutral-900/40 space-y-1 text-neutral-300">
                                        <div className="flex justify-between">
                                             <span className="text-neutral-400">Subtotal:</span>
                                             <span>₱{Number(selectedOrder.subtotal).toFixed(2)}</span>
                                        </div>
                                        <div className="flex justify-between">
                                             <span className="text-neutral-400">Delivery Fee:</span>
                                             <span>₱{Number(selectedOrder.delivery_fee).toFixed(2)}</span>
                                        </div>
                                        {selectedOrder.discount > 0 && (
                                             <div className="flex justify-between text-emerald-400">
                                                 <span>Discount:</span>
                                                 <span>-₱{Number(selectedOrder.discount).toFixed(2)}</span>
                                             </div>
                                        )}
                                        <div className="flex justify-between border-t border-neutral-800 pt-2 font-black text-white text-sm">
                                             <span>Grand Total:</span>
                                             <span className="text-orange-400">
                                                 ₱{Number(selectedOrder.total).toFixed(2)}
                                             </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Lifecycle State Advancement Control */}
                            <div>
                                <h4 className="font-bold text-white mb-2">Lifecycle Status Control</h4>
                                <div className="rounded-xl border border-neutral-800 bg-neutral-950/60 p-4">
                                    {getAllowedTransitions(selectedOrder.order_status).length === 0 ? (
                                        <div className="text-neutral-500 italic">
                                            Order is in terminal state ({selectedOrder.order_status}). No further transitions allowed.
                                        </div>
                                    ) : (
                                        <div className="space-y-3">
                                            <div className="text-neutral-300 text-xs">
                                                Available next transitions from <strong className="text-white">{selectedOrder.order_status}</strong>:
                                            </div>
                                            <div className="flex flex-wrap gap-2">
                                                {getAllowedTransitions(selectedOrder.order_status).map((target) => (
                                                    <button
                                                        key={target}
                                                        type="button"
                                                        onClick={() => advanceStatus(selectedOrder.id, target)}
                                                        className={`rounded-xl px-4 py-2 text-xs font-bold text-white transition shadow-md ${
                                                            target === 'cancelled'
                                                                ? 'bg-rose-600 hover:bg-rose-500'
                                                                : 'bg-orange-500 hover:bg-orange-600 shadow-orange-500/20'
                                                        }`}
                                                    >
                                                        Advance to {target.replace(/_/g, ' ')}
                                                    </button>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Status Audit History Timeline */}
                            {selectedOrder.status_history && selectedOrder.status_history.length > 0 && (
                                <div>
                                    <h4 className="font-bold text-white mb-2 flex items-center gap-1.5">
                                        <Clock className="h-4 w-4 text-orange-400" />
                                        Audit Trail & Timeline
                                    </h4>
                                    <div className="space-y-2 border-l-2 border-neutral-800 pl-4 ml-2">
                                        {selectedOrder.status_history.map((hist, idx) => (
                                            <div key={idx} className="relative pb-2">
                                                <span className="absolute -left-[23px] top-1.5 h-2.5 w-2.5 rounded-full bg-orange-400 ring-4 ring-neutral-900" />
                                                <div className="flex items-center gap-2 font-semibold text-white">
                                                    <span className="capitalize">{hist.status.replace(/_/g, ' ')}</span>
                                                    <span className="rounded bg-neutral-800 px-1.5 py-0.2 text-[10px] text-neutral-400">
                                                        {hist.actor || 'system'}
                                                    </span>
                                                </div>
                                                <div className="text-[11px] text-neutral-400 mt-0.5">
                                                    {hist.comment}
                                                </div>
                                                <div className="text-[10px] text-neutral-500 mt-0.5">
                                                    {new Date(hist.created_at).toLocaleString()}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            )}

            {/* Cancel Reason Modal */}
            {cancelModalOrder && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in">
                    <div className="relative w-full max-w-md rounded-2xl border border-neutral-800 bg-neutral-900 p-6 shadow-2xl">
                        <div className="flex items-center justify-between pb-3 border-b border-neutral-800">
                            <h3 className="text-lg font-bold text-rose-400 flex items-center gap-2">
                                <Ban className="h-5 w-5" /> Cancel Order #{cancelModalOrder.order_number}
                            </h3>
                            <button
                                type="button"
                                onClick={() => setCancelModalOrder(null)}
                                className="rounded-lg p-1.5 text-neutral-400 hover:bg-neutral-800 hover:text-white"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        <form onSubmit={handleCancelOrder} className="mt-4 space-y-4">
                            <div>
                                <label className="block text-xs font-medium text-neutral-300 mb-1">
                                    Cancellation Reason
                                </label>
                                <textarea
                                    required
                                    value={cancelReason}
                                    onChange={(e) => setCancelReason(e.target.value)}
                                    placeholder="e.g. Customer called to cancel, out of stock ingredients, etc."
                                    rows={3}
                                    className="w-full rounded-xl border border-neutral-800 bg-neutral-950 p-3 text-xs text-neutral-200 focus:border-rose-500 focus:outline-none"
                                />
                            </div>

                            <div className="flex justify-end gap-2 pt-2">
                                <button
                                    type="button"
                                    onClick={() => setCancelModalOrder(null)}
                                    className="rounded-xl border border-neutral-800 px-4 py-2 text-xs font-semibold text-neutral-400 hover:bg-neutral-800"
                                >
                                    Dismiss
                                </button>
                                <button
                                    type="submit"
                                    className="rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-500 transition"
                                >
                                    Confirm Cancellation
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
