import AdminLayout from '@/layouts/admin-layout';
import { Head, router } from '@inertiajs/react';
import {
    CheckCircle2,
    ChefHat,
    Clock,
    DollarSign,
    Edit3,
    Eye,
    Filter,
    Layers,
    LayoutGrid,
    List,
    Plus,
    Search,
    Trash2,
    UtensilsCrossed,
    X,
    XCircle,
} from 'lucide-react';
import React, { useState } from 'react';

interface Category {
    id: string;
    name: string;
    slug: string;
}

interface MenuItem {
    id: string;
    category_id: string;
    category?: Category;
    name: string;
    description?: string;
    price: number;
    image_url?: string;
    availability: boolean;
    preparation_time: number;
}

interface MenuItemsIndexProps {
    items: {
        data: MenuItem[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    categories: Category[];
    filters: {
        search: string;
        category_id: string;
        availability: string;
    };
}

export default function MenuItemsIndex({ items, categories, filters }: MenuItemsIndexProps) {
    const [viewMode, setViewMode] = useState<'grid' | 'table'>('grid');
    const [search, setSearch] = useState(filters.search || '');
    const [modalMode, setModalMode] = useState<'create' | 'edit' | null>(null);
    const [activeItem, setActiveItem] = useState<MenuItem | null>(null);

    const [formCategory, setFormCategory] = useState('');
    const [formName, setFormName] = useState('');
    const [formDescription, setFormDescription] = useState('');
    const [formPrice, setFormPrice] = useState('');
    const [formImage, setFormImage] = useState('');
    const [formPrepTime, setFormPrepTime] = useState('15');
    const [formAvailability, setFormAvailability] = useState(true);

    const handleFilter = (key: string, value: string) => {
        router.get(
            '/admin/menu-items',
            { ...filters, [key]: value, page: 1 },
            { preserveState: true, replace: true }
        );
    };

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        handleFilter('search', search);
    };

    const openCreateModal = () => {
        setActiveItem(null);
        setFormCategory(categories[0]?.id || '');
        setFormName('');
        setFormDescription('');
        setFormPrice('');
        setFormImage('');
        setFormPrepTime('15');
        setFormAvailability(true);
        setModalMode('create');
    };

    const openEditModal = (item: MenuItem) => {
        setActiveItem(item);
        setFormCategory(item.category_id || categories[0]?.id || '');
        setFormName(item.name);
        setFormDescription(item.description || '');
        setFormPrice(String(item.price));
        setFormImage(item.image_url || '');
        setFormPrepTime(String(item.preparation_time || 15));
        setFormAvailability(item.availability);
        setModalMode('edit');
    };

    const handleSaveItem = (e: React.FormEvent) => {
        e.preventDefault();
        const payload = {
            category_id: formCategory,
            name: formName,
            description: formDescription,
            price: parseFloat(formPrice),
            image_url: formImage || null,
            preparation_time: parseInt(formPrepTime, 10),
            availability: formAvailability,
        };

        if (modalMode === 'create') {
            router.post('/admin/menu-items', payload, {
                onSuccess: () => setModalMode(null),
            });
        } else if (modalMode === 'edit' && activeItem) {
            router.put(`/admin/menu-items/${activeItem.id}`, payload, {
                onSuccess: () => setModalMode(null),
            });
        }
    };

    const toggleAvailability = (itemId: string) => {
        router.patch(`/admin/menu-items/${itemId}/availability`, {}, { preserveScroll: true });
    };

    const handleDelete = (item: MenuItem) => {
        if (confirm(`Are you sure you want to delete "${item.name}"?`)) {
            router.delete(`/admin/menu-items/${item.id}`);
        }
    };

    return (
        <AdminLayout title="Menu Catalog">
            <Head title="Menu Items - FoodieOS Admin" />

            <div className="space-y-6">
                {/* Top Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-black tracking-tight text-white flex items-center gap-2.5">
                            Menu Items Catalog
                            <span className="rounded-lg bg-orange-500/10 px-2.5 py-0.5 text-xs font-bold text-orange-400 border border-orange-500/20">
                                {items.total} Dishes
                            </span>
                        </h1>
                        <p className="text-xs text-neutral-400 mt-1">
                            Control dish prices, ingredients, prep times, and instant in-stock / sold-out switches.
                        </p>
                    </div>

                    <div className="flex items-center gap-2">
                        <div className="flex items-center rounded-xl border border-neutral-800 bg-neutral-900/60 p-1">
                            <button
                                type="button"
                                onClick={() => setViewMode('grid')}
                                className={`rounded-lg p-1.5 transition ${
                                    viewMode === 'grid' ? 'bg-orange-500 text-white shadow-sm' : 'text-neutral-400 hover:text-white'
                                }`}
                                title="Grid View"
                            >
                                <LayoutGrid className="h-4 w-4" />
                            </button>
                            <button
                                type="button"
                                onClick={() => setViewMode('table')}
                                className={`rounded-lg p-1.5 transition ${
                                    viewMode === 'table' ? 'bg-orange-500 text-white shadow-sm' : 'text-neutral-400 hover:text-white'
                                }`}
                                title="Table View"
                            >
                                <List className="h-4 w-4" />
                            </button>
                        </div>

                        <button
                            type="button"
                            onClick={openCreateModal}
                            className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-orange-500/20 hover:from-orange-600 hover:to-amber-600 transition"
                        >
                            <Plus className="h-4 w-4" /> Add New Dish
                        </button>
                    </div>
                </div>

                {/* Filter & Search Bar */}
                <div className="rounded-2xl border border-neutral-800 bg-neutral-900/60 p-4 space-y-4">
                    {/* Category Filter Chips */}
                    <div className="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-thin">
                        <button
                            type="button"
                            onClick={() => handleFilter('category_id', 'all')}
                            className={`rounded-xl px-3.5 py-1.5 text-xs font-semibold whitespace-nowrap transition ${
                                filters.category_id === 'all'
                                    ? 'bg-orange-500 text-white shadow-md shadow-orange-500/20'
                                    : 'bg-neutral-800/60 text-neutral-400 hover:bg-neutral-800 hover:text-white'
                            }`}
                        >
                            All Categories
                        </button>
                        {categories.map((cat) => (
                            <button
                                key={cat.id}
                                type="button"
                                onClick={() => handleFilter('category_id', cat.id)}
                                className={`rounded-xl px-3.5 py-1.5 text-xs font-semibold whitespace-nowrap transition ${
                                    filters.category_id === cat.id
                                        ? 'bg-orange-500 text-white shadow-md shadow-orange-500/20'
                                        : 'bg-neutral-800/60 text-neutral-400 hover:bg-neutral-800 hover:text-white'
                                }`}
                            >
                                {cat.name}
                            </button>
                        ))}
                    </div>

                    {/* Search & Stock Filter */}
                    <div className="flex flex-col sm:flex-row items-center gap-3">
                        <form onSubmit={handleSearchSubmit} className="relative flex-1 w-full">
                            <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-500" />
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search dishes by name or ingredients..."
                                className="w-full rounded-xl border border-neutral-800 bg-neutral-950/80 pl-10 pr-4 py-2 text-xs text-neutral-200 placeholder-neutral-500 focus:border-orange-500 focus:outline-none"
                            />
                        </form>

                        <select
                            value={filters.availability || 'all'}
                            onChange={(e) => handleFilter('availability', e.target.value)}
                            className="rounded-xl border border-neutral-800 bg-neutral-950/80 px-3 py-2 text-xs text-neutral-300 focus:border-orange-500 focus:outline-none w-full sm:w-auto"
                        >
                            <option value="all">All Availability</option>
                            <option value="true">In Stock</option>
                            <option value="false">Out of Stock</option>
                        </select>
                    </div>
                </div>

                {/* Grid View */}
                {viewMode === 'grid' ? (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                        {items.data.length === 0 ? (
                            <div className="col-span-full py-12 text-center text-neutral-500 rounded-2xl border border-neutral-800 bg-neutral-900/40">
                                No dishes found matching the filter criteria.
                            </div>
                        ) : (
                            items.data.map((item) => (
                                <div
                                    key={item.id}
                                    className="group rounded-2xl border border-neutral-800 bg-neutral-900/60 overflow-hidden shadow-sm hover:border-neutral-700 transition flex flex-col"
                                >
                                    {/* Food Image */}
                                    <div className="relative h-44 w-full bg-neutral-800 overflow-hidden">
                                        {item.image_url ? (
                                            <img
                                                src={item.image_url}
                                                alt={item.name}
                                                className="h-full w-full object-cover group-hover:scale-105 transition duration-500"
                                                onError={(e) => {
                                                    (e.target as HTMLElement).style.display = 'none';
                                                }}
                                            />
                                        ) : (
                                            <div className="flex h-full w-full items-center justify-center text-neutral-600">
                                                <UtensilsCrossed className="h-10 w-10" />
                                            </div>
                                        )}

                                        <div className="absolute top-3 left-3">
                                            <span className="rounded-full border border-neutral-900/60 bg-neutral-900/80 px-2.5 py-1 text-[11px] font-semibold text-neutral-200 backdrop-blur-md">
                                                {item.category?.name || 'Uncategorized'}
                                            </span>
                                        </div>

                                        <div className="absolute top-3 right-3">
                                            <button
                                                type="button"
                                                onClick={() => toggleAvailability(item.id)}
                                                className={`rounded-full px-2.5 py-1 text-[11px] font-bold border backdrop-blur-md shadow transition ${
                                                    item.availability
                                                        ? 'bg-emerald-500/90 text-white border-emerald-400'
                                                        : 'bg-rose-500/90 text-white border-rose-400'
                                                }`}
                                            >
                                                {item.availability ? 'In Stock' : 'Sold Out'}
                                            </button>
                                        </div>
                                    </div>

                                    {/* Dish Information */}
                                    <div className="p-4 flex-1 flex flex-col justify-between">
                                        <div>
                                            <div className="flex items-start justify-between gap-2">
                                                <h3 className="font-bold text-white text-sm leading-snug">
                                                    {item.name}
                                                </h3>
                                                <span className="text-base font-black text-orange-400 shrink-0">
                                                    ₱{Number(item.price).toFixed(2)}
                                                </span>
                                            </div>
                                            <p className="text-xs text-neutral-400 mt-1 line-clamp-2">
                                                {item.description || 'Fresh ingredients prepared by gourmet kitchen chefs.'}
                                            </p>
                                        </div>

                                        <div className="mt-4 pt-3 border-t border-neutral-800 flex items-center justify-between">
                                            <div className="flex items-center gap-1 text-[11px] text-neutral-400">
                                                <Clock className="h-3.5 w-3.5 text-orange-400" />
                                                <span>{item.preparation_time} mins prep</span>
                                            </div>

                                            <div className="flex items-center gap-1">
                                                <button
                                                    type="button"
                                                    onClick={() => openEditModal(item)}
                                                    className="rounded-lg p-1.5 text-neutral-400 hover:bg-neutral-800 hover:text-white transition"
                                                    title="Edit Dish"
                                                >
                                                    <Edit3 className="h-4 w-4" />
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => handleDelete(item)}
                                                    className="rounded-lg p-1.5 text-neutral-500 hover:text-rose-400 hover:bg-rose-500/10 transition"
                                                    title="Delete Dish"
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                ) : (
                    /* Table View */
                    <div className="rounded-2xl border border-neutral-800 bg-neutral-900/60 overflow-hidden shadow-sm">
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-xs">
                                <thead className="border-b border-neutral-800 bg-neutral-900/90 text-neutral-400 font-semibold uppercase tracking-wider text-[11px]">
                                    <tr>
                                        <th className="px-5 py-3.5">Dish</th>
                                        <th className="px-5 py-3.5">Category</th>
                                        <th className="px-5 py-3.5">Price</th>
                                        <th className="px-5 py-3.5">Prep Time</th>
                                        <th className="px-5 py-3.5">Stock Status</th>
                                        <th className="px-5 py-3.5 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-800/60">
                                    {items.data.map((item) => (
                                        <tr key={item.id} className="hover:bg-neutral-800/30 transition">
                                            <td className="px-5 py-3.5 font-semibold text-white flex items-center gap-3">
                                                {item.image_url ? (
                                                    <img
                                                        src={item.image_url}
                                                        alt={item.name}
                                                        className="h-9 w-9 rounded-lg object-cover"
                                                    />
                                                ) : (
                                                    <div className="h-9 w-9 rounded-lg bg-neutral-800 flex items-center justify-center text-neutral-500">
                                                        <UtensilsCrossed className="h-4 w-4" />
                                                    </div>
                                                )}
                                                <div>
                                                    <div>{item.name}</div>
                                                    <div className="text-[11px] text-neutral-400 truncate max-w-xs">
                                                        {item.description}
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-5 py-3.5 text-neutral-300">
                                                {item.category?.name || '—'}
                                            </td>
                                            <td className="px-5 py-3.5 font-bold text-white text-sm">
                                                ₱{Number(item.price).toFixed(2)}
                                            </td>
                                            <td className="px-5 py-3.5 text-neutral-400">
                                                {item.preparation_time} min
                                            </td>
                                            <td className="px-5 py-3.5">
                                                <button
                                                    type="button"
                                                    onClick={() => toggleAvailability(item.id)}
                                                    className={`rounded-full px-2.5 py-0.5 text-xs font-semibold border ${
                                                        item.availability
                                                            ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                                                            : 'bg-rose-500/10 text-rose-400 border-rose-500/20'
                                                    }`}
                                                >
                                                    {item.availability ? 'In Stock' : 'Sold Out'}
                                                </button>
                                            </td>
                                            <td className="px-5 py-3.5 text-right space-x-1">
                                                <button
                                                    type="button"
                                                    onClick={() => openEditModal(item)}
                                                    className="rounded-lg p-1.5 text-neutral-400 hover:bg-neutral-800 hover:text-white transition"
                                                >
                                                    <Edit3 className="h-4 w-4" />
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => handleDelete(item)}
                                                    className="rounded-lg p-1.5 text-neutral-500 hover:text-rose-400 hover:bg-rose-500/10 transition"
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}
            </div>

            {/* Create / Edit Modal */}
            {modalMode && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in">
                    <div className="relative w-full max-w-lg rounded-2xl border border-neutral-800 bg-neutral-900 p-6 shadow-2xl">
                        <div className="flex items-center justify-between pb-3 border-b border-neutral-800">
                            <h3 className="text-lg font-bold text-white flex items-center gap-2">
                                <ChefHat className="h-5 w-5 text-orange-400" />
                                {modalMode === 'create' ? 'Add New Menu Item' : `Edit: ${activeItem?.name}`}
                            </h3>
                            <button
                                type="button"
                                onClick={() => setModalMode(null)}
                                className="rounded-lg p-1.5 text-neutral-400 hover:bg-neutral-800 hover:text-white"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        <form onSubmit={handleSaveItem} className="mt-4 space-y-4 text-xs">
                            <div className="grid grid-cols-2 gap-4">
                                <div className="col-span-2">
                                    <label className="block font-medium text-neutral-300 mb-1">
                                        Dish Name *
                                    </label>
                                    <input
                                        type="text"
                                        required
                                        value={formName}
                                        onChange={(e) => setFormName(e.target.value)}
                                        placeholder="e.g. Sinigang na Baboy sa Sampalok"
                                        className="w-full rounded-xl border border-neutral-800 bg-neutral-950 p-2.5 text-neutral-200 focus:border-orange-500 focus:outline-none"
                                    />
                                </div>

                                <div>
                                    <label className="block font-medium text-neutral-300 mb-1">
                                        Category *
                                    </label>
                                    <select
                                        required
                                        value={formCategory}
                                        onChange={(e) => setFormCategory(e.target.value)}
                                        className="w-full rounded-xl border border-neutral-800 bg-neutral-950 p-2.5 text-neutral-200 focus:border-orange-500 focus:outline-none"
                                    >
                                        {categories.map((c) => (
                                            <option key={c.id} value={c.id}>
                                                {c.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="block font-medium text-neutral-300 mb-1">
                                        Price (₱ PHP) *
                                    </label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        required
                                        value={formPrice}
                                        onChange={(e) => setFormPrice(e.target.value)}
                                        placeholder="280.00"
                                        className="w-full rounded-xl border border-neutral-800 bg-neutral-950 p-2.5 text-neutral-200 focus:border-orange-500 focus:outline-none"
                                    />
                                </div>

                                <div>
                                    <label className="block font-medium text-neutral-300 mb-1">
                                        Preparation Time (mins) *
                                    </label>
                                    <input
                                        type="number"
                                        required
                                        min={1}
                                        value={formPrepTime}
                                        onChange={(e) => setFormPrepTime(e.target.value)}
                                        className="w-full rounded-xl border border-neutral-800 bg-neutral-950 p-2.5 text-neutral-200 focus:border-orange-500 focus:outline-none"
                                    />
                                </div>

                                <div className="flex items-center pt-5">
                                    <label className="flex items-center gap-2 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            checked={formAvailability}
                                            onChange={(e) => setFormAvailability(e.target.checked)}
                                            className="h-4 w-4 rounded border-neutral-800 text-orange-500 focus:ring-orange-500 bg-neutral-950"
                                        />
                                        <span className="font-medium text-neutral-300">Available in Stock</span>
                                    </label>
                                </div>

                                <div className="col-span-2">
                                    <label className="block font-medium text-neutral-300 mb-1">
                                        Image URL (Optional)
                                    </label>
                                    <input
                                        type="url"
                                        value={formImage}
                                        onChange={(e) => setFormImage(e.target.value)}
                                        placeholder="https://images.unsplash.com/..."
                                        className="w-full rounded-xl border border-neutral-800 bg-neutral-950 p-2.5 text-neutral-200 focus:border-orange-500 focus:outline-none"
                                    />
                                </div>

                                <div className="col-span-2">
                                    <label className="block font-medium text-neutral-300 mb-1">
                                        Description & Ingredients
                                    </label>
                                    <textarea
                                        rows={3}
                                        value={formDescription}
                                        onChange={(e) => setFormDescription(e.target.value)}
                                        placeholder="Brief description of flavors and ingredients..."
                                        className="w-full rounded-xl border border-neutral-800 bg-neutral-950 p-2.5 text-neutral-200 focus:border-orange-500 focus:outline-none"
                                    />
                                </div>
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
                                    {modalMode === 'create' ? 'Create Dish' : 'Save Changes'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
