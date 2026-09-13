import { Link, usePage } from '@inertiajs/react';
import {
    BarChart3,
    BookOpen,
    CheckCircle2,
    ChefHat,
    ExternalLink,
    Layers,
    Menu,
    Package,
    PanelLeftClose,
    PanelLeftOpen,
    Search,
    ShoppingBag,
    Store,
    Users,
    UtensilsCrossed,
    X,
} from 'lucide-react';
import React, { useState } from 'react';

interface AdminLayoutProps {
    title: string;
    children: React.ReactNode;
}

export default function AdminLayout({ title, children }: AdminLayoutProps) {
    const { url, props } = usePage<{
        flash?: { success?: string; error?: string };
        errors?: Record<string, string>;
    }>();

    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [sidebarCollapsed, setSidebarCollapsed] = useState(false);

    const navItems = [
        {
            name: 'Dashboard',
            href: '/admin/dashboard',
            icon: BarChart3,
            current: url === '/admin/dashboard' || url === '/admin',
        },
        {
            name: 'Orders',
            href: '/admin/orders',
            icon: ShoppingBag,
            current: url.startsWith('/admin/orders'),
        },
        {
            name: 'Menu Items',
            href: '/admin/menu-items',
            icon: ChefHat,
            current: url.startsWith('/admin/menu-items'),
        },
        {
            name: 'Categories',
            href: '/admin/categories',
            icon: Layers,
            current: url.startsWith('/admin/categories'),
        },
        {
            name: 'Customers',
            href: '/admin/customers',
            icon: Users,
            current: url.startsWith('/admin/customers'),
        },
    ];

    return (
        <div className="min-h-screen bg-neutral-950 text-neutral-100 flex flex-col font-sans selection:bg-orange-500 selection:text-white">
            {/* Top Navigation Header */}
            <header className="sticky top-0 z-40 flex h-16 items-center justify-between border-b border-neutral-800/80 bg-neutral-900/90 px-4 sm:px-6 backdrop-blur-md">
                <div className="flex items-center gap-3">
                    <button
                        type="button"
                        onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                        className="rounded-lg p-2 text-neutral-400 hover:bg-neutral-800 hover:text-white md:hidden"
                        aria-label="Toggle Navigation"
                    >
                        {mobileMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
                    </button>

                    <button
                        type="button"
                        onClick={() => setSidebarCollapsed(!sidebarCollapsed)}
                        className="hidden rounded-lg p-2 text-neutral-400 hover:bg-neutral-800 hover:text-white md:inline-flex"
                        aria-label="Collapse Sidebar"
                    >
                        {sidebarCollapsed ? <PanelLeftOpen className="h-5 w-5" /> : <PanelLeftClose className="h-5 w-5" />}
                    </button>

                    <Link href="/admin/dashboard" className="flex items-center gap-2.5">
                        <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-amber-500 to-orange-500 text-white shadow-lg shadow-orange-500/20">
                            <UtensilsCrossed className="h-5 w-5" />
                        </div>
                        <div className="flex flex-col">
                            <span className="text-base font-bold tracking-tight text-white flex items-center gap-1.5">
                                Foodie<span className="text-orange-400">OS</span>
                                <span className="rounded-md bg-orange-500/10 px-1.5 py-0.5 text-[10px] font-semibold text-orange-400 border border-orange-500/20">
                                    ADMIN
                                </span>
                            </span>
                        </div>
                    </Link>
                </div>

                <div className="flex items-center gap-3">
                    <div className="hidden sm:flex items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs text-emerald-400 font-medium">
                        <span className="h-2 w-2 rounded-full bg-emerald-400 animate-pulse" />
                        MongoDB Cluster Online
                    </div>

                    <a
                        href="/api/documentation"
                        target="_blank"
                        rel="noreferrer"
                        className="flex items-center gap-1.5 rounded-lg border border-neutral-700/70 bg-neutral-800/80 px-3 py-1.5 text-xs font-semibold text-neutral-200 transition hover:bg-neutral-700 hover:text-white hover:border-neutral-600 shadow-sm"
                    >
                        <BookOpen className="h-3.5 w-3.5 text-orange-400" />
                        Swagger API Docs
                        <ExternalLink className="h-3 w-3 text-neutral-400" />
                    </a>

                    <Link
                        href="/"
                        className="flex items-center gap-1.5 rounded-lg border border-neutral-800 bg-neutral-900/50 px-3 py-1.5 text-xs font-medium text-neutral-400 transition hover:bg-neutral-800 hover:text-neutral-200"
                    >
                        <Store className="h-3.5 w-3.5" />
                        Storefront
                    </Link>
                </div>
            </header>

            <div className="flex flex-1 overflow-hidden">
                {/* Desktop Sidebar */}
                <aside
                    className={`hidden md:flex flex-col border-r border-neutral-800/80 bg-neutral-900/40 transition-all duration-300 ${
                        sidebarCollapsed ? 'w-20' : 'w-64'
                    }`}
                >
                    <nav className="flex-1 space-y-1.5 p-4">
                        {navItems.map((item) => {
                            const Icon = item.icon;
                            return (
                                <Link
                                    key={item.name}
                                    href={item.href}
                                    className={`group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition-all ${
                                        item.current
                                            ? 'bg-gradient-to-r from-orange-500/20 to-amber-500/10 text-orange-400 border border-orange-500/30 shadow-sm shadow-orange-500/5'
                                            : 'text-neutral-400 hover:bg-neutral-800/60 hover:text-neutral-200'
                                    }`}
                                    title={sidebarCollapsed ? item.name : undefined}
                                >
                                    <Icon
                                        className={`h-5 w-5 shrink-0 transition-colors ${
                                            item.current ? 'text-orange-400' : 'text-neutral-400 group-hover:text-neutral-200'
                                        }`}
                                    />
                                    {!sidebarCollapsed && <span>{item.name}</span>}
                                </Link>
                            );
                        })}
                    </nav>

                    {/* Sidebar Footer */}
                    <div className="p-4 border-t border-neutral-800/70">
                        <div className={`rounded-xl border border-neutral-800 bg-neutral-900/60 p-3 text-xs text-neutral-400 ${sidebarCollapsed ? 'text-center' : ''}`}>
                            {!sidebarCollapsed ? (
                                <>
                                    <div className="font-semibold text-neutral-300">Food Ordering Engine</div>
                                    <div className="text-[11px] text-neutral-400">Laravel 13 & NoSQL Mongo</div>
                                </>
                            ) : (
                                <span className="font-bold text-orange-400">v1.0</span>
                            )}
                        </div>
                    </div>
                </aside>

                {/* Mobile Drawer */}
                {mobileMenuOpen && (
                    <div className="fixed inset-0 z-50 flex md:hidden">
                        <div className="fixed inset-0 bg-black/70 backdrop-blur-sm" onClick={() => setMobileMenuOpen(false)} />
                        <div className="relative flex w-72 flex-col bg-neutral-900 p-4 border-r border-neutral-800">
                            <div className="flex items-center justify-between pb-4 mb-2 border-b border-neutral-800">
                                <span className="text-base font-bold text-white flex items-center gap-2">
                                    <UtensilsCrossed className="h-5 w-5 text-orange-400" />
                                    FoodieOS Admin
                                </span>
                                <button
                                    type="button"
                                    onClick={() => setMobileMenuOpen(false)}
                                    className="rounded-lg p-1.5 text-neutral-400 hover:bg-neutral-800 hover:text-white"
                                >
                                    <X className="h-5 w-5" />
                                </button>
                            </div>

                            <nav className="flex-1 space-y-1">
                                {navItems.map((item) => {
                                    const Icon = item.icon;
                                    return (
                                        <Link
                                            key={item.name}
                                            href={item.href}
                                            onClick={() => setMobileMenuOpen(false)}
                                            className={`flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium ${
                                                item.current
                                                    ? 'bg-orange-500/20 text-orange-400 border border-orange-500/30'
                                                    : 'text-neutral-400 hover:bg-neutral-800 hover:text-neutral-200'
                                            }`}
                                        >
                                            <Icon className="h-5 w-5" />
                                            {item.name}
                                        </Link>
                                    );
                                })}
                            </nav>
                        </div>
                    </div>
                )}

                {/* Main Content Area */}
                <main className="flex-1 overflow-y-auto bg-neutral-950 p-4 sm:p-6 lg:p-8">
                    {/* Flash Messages */}
                    {props.flash?.success && (
                        <div className="mb-6 flex items-center gap-2.5 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300 shadow-sm animate-in fade-in slide-in-from-top-2">
                            <CheckCircle2 className="h-5 w-5 text-emerald-400 shrink-0" />
                            <span>{props.flash.success}</span>
                        </div>
                    )}

                    {props.errors && Object.keys(props.errors).length > 0 && (
                        <div className="mb-6 rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-300 shadow-sm">
                            <div className="font-semibold mb-1">Please fix the following issues:</div>
                            <ul className="list-disc list-inside space-y-0.5 text-xs text-rose-200">
                                {Object.values(props.errors).map((err, i) => (
                                    <li key={i}>{err}</li>
                                ))}
                            </ul>
                        </div>
                    )}

                    <div className="mx-auto max-w-7xl">
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}
