import AdminLayout from '@/layouts/admin-layout';
import { Head, Link, router } from '@inertiajs/react';
import {
    CheckCircle2,
    Edit3,
    Layers,
    Plus,
    Search,
    Trash2,
    UtensilsCrossed,
    X,
    XCircle,
} from 'lucide-react';
import React, { useState } from 'react';

interface CategoryItem {
    id: string;
    name: string;
    slug: string;
    description?: string;
    image_url?: string;
    status: string;
    menu_items_count: number;
    created_at?: string;
}

interface CategoriesIndexProps {
    categories: CategoryItem[];
    filters: {
        search: string;
    };
}

export default function CategoriesIndex({ categories, filters }: CategoriesIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [modalMode, setModalMode] = useState<'create' | 'edit' | null>(null);
    const [activeCategory, setActiveCategory] = useState<CategoryItem | null>(null);

    const [formName, setFormName] = useState('');
    const [formDescription, setFormDescription] = useState('');
    const [formImage, setFormImage] = useState('');
    const [formStatus, setFormStatus] = useState('active');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/admin/categories', { search }, { preserveState: true, replace: true });
    };

    const openCreateModal = () => {
        setActiveCategory(null);
        setFormName('');
        setFormDescription('');
        setFormImage('');
        setFormStatus('active');
        setModalMode('create');
    };

    const openEditModal = (cat: CategoryItem) => {
        setActiveCategory(cat);
        setFormName(cat.name);
        setFormDescription(cat.description || '');
        setFormImage(cat.image_url || '');
        setFormStatus(cat.status);
        setModalMode('edit');
    };

    const handleSave = (e: React.FormEvent) => {
        e.preventDefault();
        const payload = {
            name: formName,
            description: formDescription,
            image_url: formImage || null,
            status: formStatus,
        };

        if (modalMode === 'create') {
            router.post('/admin/categories', payload, {
                onSuccess: () => setModalMode(null),
            });
        } else if (modalMode === 'edit' && activeCategory) {
            router.put(`/admin/categories/${activeCategory.id}`, payload, {
                onSuccess: () => setModalMode(null),
            });
        }
    };

    const toggleStatus = (id: string) => {
        router.patch(`/admin/categories/${id}/status`, {}, { preserveScroll: true });
    };

    const handleDelete = (cat: CategoryItem) => {
        if (confirm(`Are you sure you want to delete category "${cat.name}"?`)) {
            router.delete(`/admin/categories/${cat.id}`);
        }
    };

    return (
        <AdminLayout title="Categories Manager">
            <Head title="Categories - FoodieOS Admin" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-black tracking-tight text-white flex items-center gap-2.5">
                            Food Categories
                            <span className="rounded-lg bg-orange-500/10 px-2.5 py-0.5 text-xs font-bold text-orange-400 border border-orange-500/20">
                                {categories.length} Total
                            </span>
                        </h1>
                        <p className="text-xs text-neutral-400 mt-1">
                            Organize your menu into appetizing cuisine sections and dietary groups.
                        </p>
                    </div>

                    <button
                        type="button"
                        onClick={openCreateModal}
                        className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-orange-500/20 hover:from-orange-600 hover:to-amber-600 transition"
                    >
                        <Plus className="h-4 w-4" /> Add New Category
                    </button>
                </div>

                {/* Search Bar */}
                <div className="rounded-2xl border border-neutral-800 bg-neutral-900/60 p-4">
                    <form onSubmit={handleSearch} className="relative w-full">
                        <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-500" />
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Search categories..."
                            className="w-full rounded-xl border border-neutral-800 bg-neutral-950/80 pl-10 pr-4 py-2 text-xs text-neutral-200 placeholder-neutral-500 focus:border-orange-500 focus:outline-none"
                        />
                    </form>
                </div>

                {/* Categories Grid */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    {categories.length === 0 ? (
                        <div className="col-span-full py-12 text-center text-neutral-500 rounded-2xl border border-neutral-800 bg-neutral-900/40">
                            No categories found.
                        </div>
                    ) : (
                        categories.map((cat) => (
                            <div
                                key={cat.id}
                                className="group rounded-2xl border border-neutral-800 bg-neutral-900/60 overflow-hidden shadow-sm hover:border-neutral-700 transition flex flex-col justify-between"
                            >
                                <div>
                                    {/* Cover Image */}
                                    <div className="relative h-36 w-full bg-neutral-800 overflow-hidden">
                                        {cat.image_url ? (
                                            <img
                                                src={cat.image_url}
                                                alt={cat.name}
                                                className="h-full w-full object-cover group-hover:scale-105 transition duration-500"
                                                onError={(e) => {
                                                    (e.target as HTMLElement).style.display = 'none';
                                                }}
                                            />
                                        ) : (
                                            <div className="flex h-full w-full items-center justify-center text-neutral-600">
                                                <Layers className="h-10 w-10" />
                                            </div>
                                        )}

                                        <div className="absolute top-3 left-3">
                                            <span className="rounded-full border border-neutral-900/60 bg-neutral-900/80 px-2.5 py-1 text-[11px] font-bold text-orange-400 backdrop-blur-md">
                                                {cat.menu_items_count} Dishes
                                            </span>
                                        </div>

                                        <div className="absolute top-3 right-3">
                                            <button
                                                type="button"
                                                onClick={() => toggleStatus(cat.id)}
                                                className={`rounded-full px-2.5 py-1 text-[11px] font-bold border backdrop-blur-md transition ${
                                                    cat.status === 'active'
                                                        ? 'bg-emerald-500/90 text-white border-emerald-400'
                                                        : 'bg-neutral-800/90 text-neutral-400 border-neutral-700'
                                                }`}
                                            >
                                                {cat.status === 'active' ? 'Active' : 'Inactive'}
                                            </button>
                                        </div>
                                    </div>

                                    {/* Info */}
                                    <div className="p-5">
                                        <div className="flex items-center justify-between gap-2">
                                            <h3 className="text-base font-bold text-white group-hover:text-orange-400 transition">
                                                {cat.name}
                                            </h3>
                                            <span className="font-mono text-[10px] text-neutral-500 bg-neutral-800 px-2 py-0.5 rounded">
                                                {cat.slug}
                                            </span>
                                        </div>
                                        <p className="text-xs text-neutral-400 mt-2 line-clamp-2">
                                            {cat.description || 'Gourmet category section.'}
                                        </p>
                                    </div>
                                </div>

                                {/* Actions */}
                                <div className="p-5 pt-0 border-t border-neutral-800/60 mt-2 flex items-center justify-between">
                                    <Link
                                        href={`/admin/menu-items?category_id=${cat.id}`}
                                        className="text-xs font-semibold text-orange-400 hover:text-orange-300"
                                    >
                                        View Dishes &rarr;
                                    </Link>

                                    <div className="flex items-center gap-1">
                                        <button
                                            type="button"
                                            onClick={() => openEditModal(cat)}
                                            className="rounded-lg p-1.5 text-neutral-400 hover:bg-neutral-800 hover:text-white transition"
                                            title="Edit Category"
                                        >
                                            <Edit3 className="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => handleDelete(cat)}
                                            className="rounded-lg p-1.5 text-neutral-500 hover:text-rose-400 hover:bg-rose-500/10 transition"
                                            title="Delete Category"
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))
                    )}
                </div>
            </div>

            {/* Modal */}
            {modalMode && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in">
                    <div className="relative w-full max-w-md rounded-2xl border border-neutral-800 bg-neutral-900 p-6 shadow-2xl">
                        <div className="flex items-center justify-between pb-3 border-b border-neutral-800">
                            <h3 className="text-lg font-bold text-white flex items-center gap-2">
                                <Layers className="h-5 w-5 text-orange-400" />
                                {modalMode === 'create' ? 'Create Category' : `Edit: ${activeCategory?.name}`}
                            </h3>
                            <button
                                type="button"
                                onClick={() => setModalMode(null)}
                                className="rounded-lg p-1.5 text-neutral-400 hover:bg-neutral-800 hover:text-white"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        <form onSubmit={handleSave} className="mt-4 space-y-4 text-xs">
                            <div>
                                <label className="block font-medium text-neutral-300 mb-1">Category Name *</label>
                                <input
                                    type="text"
                                    required
                                    value={formName}
                                    onChange={(e) => setFormName(e.target.value)}
                                    placeholder="e.g. Mga Ulam at Lutong Bahay"
                                    className="w-full rounded-xl border border-neutral-800 bg-neutral-950 p-2.5 text-neutral-200 focus:border-orange-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label className="block font-medium text-neutral-300 mb-1">Image URL (Optional)</label>
                                <input
                                    type="url"
                                    value={formImage}
                                    onChange={(e) => setFormImage(e.target.value)}
                                    placeholder="https://images.unsplash.com/..."
                                    className="w-full rounded-xl border border-neutral-800 bg-neutral-950 p-2.5 text-neutral-200 focus:border-orange-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label className="block font-medium text-neutral-300 mb-1">Status *</label>
                                <select
                                    value={formStatus}
                                    onChange={(e) => setFormStatus(e.target.value)}
                                    className="w-full rounded-xl border border-neutral-800 bg-neutral-950 p-2.5 text-neutral-200 focus:border-orange-500 focus:outline-none"
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <div>
                                <label className="block font-medium text-neutral-300 mb-1">Description</label>
                                <textarea
                                    rows={3}
                                    value={formDescription}
                                    onChange={(e) => setFormDescription(e.target.value)}
                                    placeholder="Section description..."
                                    className="w-full rounded-xl border border-neutral-800 bg-neutral-950 p-2.5 text-neutral-200 focus:border-orange-500 focus:outline-none"
                                />
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
                                    {modalMode === 'create' ? 'Create' : 'Save'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
