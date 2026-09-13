import AdminLayout from '@/layouts/admin-layout';
import { Head, router } from '@inertiajs/react';
import {
    Ban,
    CheckCircle2,
    Clock,
    DollarSign,
    Edit3,
    Eye,
    Mail,
    MapPin,
    Phone,
    Plus,
    Search,
    ShoppingBag,
    Trash2,
    UserCheck,
    Users,
    UserX,
    X,
} from 'lucide-react';
import React, { useState } from 'react';

interface CustomerOrder {
    id: string;
    order_number: string;
    total: number;
    order_status: string;
    created_at: string;
}

interface CustomerItem {
    id: string;
    name: string;
    email: string;
    phone?: string;
    address?: string;
    status: string;
    orders_count: number;
    total_spent: number;
    orders?: CustomerOrder[];
    created_at?: string;
}

interface CustomersIndexProps {
    customers: {
        data: CustomerItem[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        search: string;
        status: string;
    };
}

export default function CustomersIndex({ customers, filters }: CustomersIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [modalMode, setModalMode] = useState<'create' | 'edit' | null>(null);
    const [activeCustomer, setActiveCustomer] = useState<CustomerItem | null>(null);
    const [viewOrdersCustomer, setViewOrdersCustomer] = useState<CustomerItem | null>(null);

    const [formName, setFormName] = useState('');
    const [formEmail, setFormEmail] = useState('');
    const [formPassword, setFormPassword] = useState('');
    const [formPhone, setFormPhone] = useState('');
    const [formAddress, setFormAddress] = useState('');
    const [formStatus, setFormStatus] = useState('active');

    const handleFilter = (key: string, value: string) => {
        router.get(
            '/admin/customers',
            { ...filters, [key]: value, page: 1 },
            { preserveState: true, replace: true }
        );
    };

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        handleFilter('search', search);
    };

    const openCreateModal = () => {
        setActiveCustomer(null);
        setFormName('');
        setFormEmail('');
        setFormPassword('password123');
        setFormPhone('');
        setFormAddress('');
        setFormStatus('active');
        setModalMode('create');
    };

    const openEditModal = (customer: CustomerItem) => {
        setActiveCustomer(customer);
        setFormName(customer.name);
        setFormEmail(customer.email);
        setFormPassword('');
        setFormPhone(customer.phone || '');
        setFormAddress(customer.address || '');
        setFormStatus(customer.status);
        setModalMode('edit');
    };

    const handleSave = (e: React.FormEvent) => {
        e.preventDefault();

        if (modalMode === 'create') {
            router.post(
                '/admin/customers',
                {
                    name: formName,
                    email: formEmail,
                    password: formPassword,
                    phone: formPhone || null,
                    address: formAddress || null,
                    status: formStatus,
                },
                {
                    onSuccess: () => setModalMode(null),
                }
            );
        } else if (modalMode === 'edit' && activeCustomer) {
            router.put(
                `/admin/customers/${activeCustomer.id}`,
                {
                    name: formName,
                    phone: formPhone || null,
                    address: formAddress || null,
                    status: formStatus,
                },
                {
                    onSuccess: () => setModalMode(null),
                }
            );
        }
    };

    const handleDelete = (customer: CustomerItem) => {
        if (confirm(`Are you sure you want to delete customer account "${customer.name}"?`)) {
            router.delete(`/admin/customers/${customer.id}`);
        }
    };

    const getStatusBadge = (status: string) => {
        const map: Record<string, { bg: string; text: string; label: string }> = {
            active: { bg: 'bg-emerald-500/10 border-emerald-500/20', text: 'text-emerald-400', label: 'Active' },
            inactive: { bg: 'bg-neutral-800 border-neutral-700', text: 'text-neutral-400', label: 'Inactive' },
            suspended: { bg: 'bg-rose-500/10 border-rose-500/20', text: 'text-rose-400', label: 'Suspended' },
        };
        const s = map[status] || map.active;
        return (
            <span className={`inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold border ${s.bg} ${s.text}`}>
                <span className="h-1.5 w-1.5 rounded-full bg-current" />
                {s.label}
            </span>
        );
    };

    return (
        <AdminLayout title="Customers Directory">
            <Head title="Customers - FoodieOS Admin" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-black tracking-tight text-white flex items-center gap-2.5">
                            Customer Directory
                            <span className="rounded-lg bg-orange-500/10 px-2.5 py-0.5 text-xs font-bold text-orange-400 border border-orange-500/20">
                                {customers.total} Registered
                            </span>
                        </h1>
                        <p className="text-xs text-neutral-400 mt-1">
                            Inspect customer ordering history, delivery addresses, and manage account statuses.
                        </p>
                    </div>

                    <button
                        type="button"
                        onClick={openCreateModal}
                        className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-orange-500/20 hover:from-orange-600 hover:to-amber-600 transition"
                    >
                        <Plus className="h-4 w-4" /> Add Customer
                    </button>
                </div>

                {/* Filter & Search */}
                <div className="rounded-2xl border border-neutral-800 bg-neutral-900/60 p-4 flex flex-col sm:flex-row items-center gap-3">
                    <form onSubmit={handleSearch} className="relative flex-1 w-full">
                        <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-500" />
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Search by customer name, email, or phone..."
                            className="w-full rounded-xl border border-neutral-800 bg-neutral-950/80 pl-10 pr-4 py-2 text-xs text-neutral-200 placeholder-neutral-500 focus:border-orange-500 focus:outline-none"
                        />
                    </form>

                    <select
                        value={filters.status || 'all'}
                        onChange={(e) => handleFilter('status', e.target.value)}
                        className="rounded-xl border border-neutral-800 bg-neutral-950/80 px-3 py-2 text-xs text-neutral-300 focus:border-orange-500 focus:outline-none w-full sm:w-auto"
                    >
                        <option value="all">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>

                {/* Customers Table */}
                <div className="rounded-2xl border border-neutral-800 bg-neutral-900/60 overflow-hidden shadow-sm">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="border-b border-neutral-800 bg-neutral-900/90 text-neutral-400 font-semibold uppercase tracking-wider text-[11px]">
                                <tr>
                                    <th className="px-5 py-3.5">Customer</th>
                                    <th className="px-5 py-3.5">Contact Details</th>
                                    <th className="px-5 py-3.5">Delivery Address</th>
                                    <th className="px-5 py-3.5">Orders</th>
                                    <th className="px-5 py-3.5">Total Spent</th>
                                    <th className="px-5 py-3.5">Status</th>
                                    <th className="px-5 py-3.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-neutral-800/60">
                                {customers.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={7} className="py-12 text-center text-neutral-500">
                                            No customers found.
                                        </td>
                                    </tr>
                                ) : (
                                    customers.data.map((customer) => (
                                        <tr key={customer.id} className="hover:bg-neutral-800/30 transition">
                                            <td className="px-5 py-4">
                                                <div className="flex items-center gap-3">
                                                    <div className="h-9 w-9 rounded-full bg-gradient-to-tr from-orange-500/20 to-amber-500/20 border border-orange-500/30 flex items-center justify-center font-bold text-orange-400 text-xs">
                                                        {customer.name
                                                            .split(' ')
                                                            .map((n) => n[0])
                                                            .join('')
                                                            .toUpperCase()
                                                            .slice(0, 2)}
                                                    </div>
                                                    <div>
                                                        <div className="font-bold text-white text-sm">{customer.name}</div>
                                                        <div className="text-[11px] text-neutral-400">{customer.email}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-5 py-4 text-neutral-300">
                                                {customer.phone || '—'}
                                            </td>
                                            <td className="px-5 py-4 text-neutral-400 max-w-[200px] truncate">
                                                {customer.address || 'No address set'}
                                            </td>
                                            <td className="px-5 py-4">
                                                <button
                                                    type="button"
                                                    onClick={() => setViewOrdersCustomer(customer)}
                                                    className="inline-flex items-center gap-1 font-bold text-orange-400 hover:text-orange-300 transition"
                                                >
                                                    <ShoppingBag className="h-3.5 w-3.5" />
                                                    {customer.orders_count} orders
                                                </button>
                                            </td>
                                            <td className="px-5 py-4 font-bold text-white text-sm">
                                                ₱{Number(customer.total_spent).toFixed(2)}
                                            </td>
                                            <td className="px-5 py-4">
                                                {getStatusBadge(customer.status)}
                                            </td>
                                            <td className="px-5 py-4 text-right space-x-1.5 whitespace-nowrap">
                                                <button
                                                    type="button"
                                                    onClick={() => setViewOrdersCustomer(customer)}
                                                    className="inline-flex items-center gap-1 rounded-lg bg-neutral-800 px-2.5 py-1.5 text-xs font-semibold text-neutral-200 hover:bg-neutral-700 hover:text-white transition"
                                                >
                                                    <Eye className="h-3.5 w-3.5" />
                                                    Orders
                                                </button>

                                                <button
                                                    type="button"
                                                    onClick={() => openEditModal(customer)}
                                                    className="rounded-lg p-1.5 text-neutral-400 hover:bg-neutral-800 hover:text-white transition"
                                                    title="Edit Customer"
                                                >
                                                    <Edit3 className="h-4 w-4" />
                                                </button>

                                                <button
                                                    type="button"
                                                    onClick={() => handleDelete(customer)}
                                                    className="rounded-lg p-1.5 text-neutral-500 hover:text-rose-400 hover:bg-rose-500/10 transition"
                                                    title="Delete Customer"
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </button>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {/* Customer Orders Modal */}
            {viewOrdersCustomer && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in">
                    <div className="relative w-full max-w-lg rounded-2xl border border-neutral-800 bg-neutral-900 p-6 shadow-2xl">
                        <div className="flex items-center justify-between pb-3 border-b border-neutral-800">
                            <div>
                                <h3 className="text-base font-bold text-white">
                                    Order History: {viewOrdersCustomer.name}
                                </h3>
                                <div className="text-xs text-neutral-400">{viewOrdersCustomer.email}</div>
                            </div>
                            <button
                                type="button"
                                onClick={() => setViewOrdersCustomer(null)}
                                className="rounded-lg p-1.5 text-neutral-400 hover:bg-neutral-800 hover:text-white"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        <div className="mt-4 space-y-2 max-h-[60vh] overflow-y-auto text-xs">
                            {(!viewOrdersCustomer.orders || viewOrdersCustomer.orders.length === 0) ? (
                                <div className="py-8 text-center text-neutral-500">
                                    No orders placed yet by this customer.
                                </div>
                            ) : (
                                viewOrdersCustomer.orders.map((ord) => (
                                    <div
                                        key={ord.id}
                                        className="flex items-center justify-between rounded-xl border border-neutral-800 bg-neutral-950 p-3.5"
                                    >
                                        <div>
                                            <div className="font-mono font-bold text-white text-xs">
                                                {ord.order_number}
                                            </div>
                                            <div className="text-[11px] text-neutral-400 mt-0.5">
                                                {new Date(ord.created_at).toLocaleDateString()}
                                            </div>
                                        </div>

                                        <div className="flex items-center gap-3">
                                            <span className="font-bold text-white">
                                                ₱{Number(ord.total).toFixed(2)}
                                            </span>
                                            <span className="rounded-full bg-orange-500/10 px-2 py-0.5 text-[10px] font-semibold text-orange-400 border border-orange-500/20 capitalize">
                                                {ord.order_status.replace(/_/g, ' ')}
                                            </span>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>

                        <div className="mt-5 pt-3 border-t border-neutral-800 flex justify-end">
                            <button
                                type="button"
                                onClick={() => setViewOrdersCustomer(null)}
                                className="rounded-xl bg-neutral-800 px-4 py-2 text-xs font-semibold text-neutral-200 hover:bg-neutral-700"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Create / Edit Modal */}
            {modalMode && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in">
                    <div className="relative w-full max-w-md rounded-2xl border border-neutral-800 bg-neutral-900 p-6 shadow-2xl">
                        <div className="flex items-center justify-between pb-3 border-b border-neutral-800">
                            <h3 className="text-lg font-bold text-white flex items-center gap-2">
                                <Users className="h-5 w-5 text-orange-400" />
                                {modalMode === 'create' ? 'Create Customer Profile' : `Edit: ${activeCustomer?.name}`}
                            </h3>
                            <button
                                type="button"
                                onClick={() => setModalMode(null)}
                                className="rounded-lg p-1.5 text-neutral-400 hover:bg-neutral-800 hover:text-white"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        <form onSubmit={handleSave} className="mt-4 space-y-3.5 text-xs">
                            <div>
                                <label className="block font-medium text-neutral-300 mb-1">Full Name *</label>
                                <input
                                    type="text"
                                    required
                                    value={formName}
                                    onChange={(e) => setFormName(e.target.value)}
                                    placeholder="e.g. Juan dela Cruz"
                                    className="w-full rounded-xl border border-neutral-800 bg-neutral-950 p-2.5 text-neutral-200 focus:border-orange-500 focus:outline-none"
                                />
                            </div>

                            {modalMode === 'create' && (
                                <>
                                    <div>
                                        <label className="block font-medium text-neutral-300 mb-1">Email Address *</label>
                                        <input
                                            type="email"
                                            required
                                            value={formEmail}
                                            onChange={(e) => setFormEmail(e.target.value)}
                                            placeholder="juan.delacruz@example.ph"
                                            className="w-full rounded-xl border border-neutral-800 bg-neutral-950 p-2.5 text-neutral-200 focus:border-orange-500 focus:outline-none"
                                        />
                                    </div>

                                    <div>
                                        <label className="block font-medium text-neutral-300 mb-1">Temporary Password *</label>
                                        <input
                                            type="password"
                                            required
                                            value={formPassword}
                                            onChange={(e) => setFormPassword(e.target.value)}
                                            placeholder="••••••••"
                                            className="w-full rounded-xl border border-neutral-800 bg-neutral-950 p-2.5 text-neutral-200 focus:border-orange-500 focus:outline-none"
                                        />
                                    </div>
                                </>
                            )}

                            <div>
                                <label className="block font-medium text-neutral-300 mb-1">Phone Number</label>
                                <input
                                    type="text"
                                    value={formPhone}
                                    onChange={(e) => setFormPhone(e.target.value)}
                                    placeholder="+63 917 123 4567"
                                    className="w-full rounded-xl border border-neutral-800 bg-neutral-950 p-2.5 text-neutral-200 focus:border-orange-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label className="block font-medium text-neutral-300 mb-1">Default Delivery Address</label>
                                <textarea
                                    rows={2}
                                    value={formAddress}
                                    onChange={(e) => setFormAddress(e.target.value)}
                                    placeholder="Unit / House No., Barangay, City, Metro Manila"
                                    className="w-full rounded-xl border border-neutral-800 bg-neutral-950 p-2.5 text-neutral-200 focus:border-orange-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label className="block font-medium text-neutral-300 mb-1">Account Status *</label>
                                <select
                                    value={formStatus}
                                    onChange={(e) => setFormStatus(e.target.value)}
                                    className="w-full rounded-xl border border-neutral-800 bg-neutral-950 p-2.5 text-neutral-200 focus:border-orange-500 focus:outline-none"
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>

                            <div className="flex justify-end gap-2 pt-3 border-t border-neutral-800">
                                <button
                                    type="button"
                                    onClick={() => setModalMode(null)}
                                    className="rounded-xl border border-neutral-800 px-4 py-2 text-xs font-semibold text-neutral-400 hover:bg-neutral-800"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    className="rounded-xl bg-orange-500 px-4 py-2 text-xs font-bold text-white hover:bg-orange-600 transition shadow-md shadow-orange-500/20"
                                >
                                    {modalMode === 'create' ? 'Create Customer' : 'Save Changes'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
