import { useState, useTransition } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import {
    Activity,
    AlertCircle,
    Building2,
    Calendar,
    Check,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    Clock,
    Edit3,
    Eye,
    Filter,
    ListFilter,
    Loader2,
    MapPin,
    Phone,
    Plus,
    Search,
    Shield,
    Sparkles,
    Stethoscope,
    Trash2,
    UserCheck,
    UserX,
    X,
    XCircle,
} from 'lucide-react';
import { toast } from 'sonner';

import {
    destroy as destroyPartnerAction,
    show as showPartnerAction,
    toggleStatus as toggleStatusAction,
    update as updatePartnerAction,
} from '@/actions/App/Http/Controllers/Admin/Partners/PartnerController';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index as partnersIndex } from '@/routes/admin/partners';

export interface UserItem {
    id: number;
    name?: string | null;
    full_name?: string | null;
    email: string;
    phone_number?: string | null;
    image_url?: string | null;
}

export interface PartnerTypeItem {
    code: string;
    en: string;
    fr?: string | null;
    ar?: string | null;
}

export interface ProfessionItem {
    code: string;
    partner_type_code?: string | null;
    en: string;
    fr?: string | null;
    ar?: string | null;
    hex: string;
}

export interface SpecialityItem {
    code: string;
    profession_code?: string | null;
    en: string;
    fr?: string | null;
    ar?: string | null;
}

export interface WilayaItem {
    code: string;
    number?: string | null;
    en?: string | null;
    fr?: string | null;
    ar?: string | null;
}

export interface CenterCatalogItem {
    code: string;
    en?: string | null;
    fr?: string | null;
    ar?: string | null;
}

export interface PartnerItem {
    id: number;
    user_id: number;
    partner_type_code?: string | null;
    name?: string | null;
    profession_code?: string | null;
    speciality_code?: string | null;
    custom_speciality?: string | null;
    center_catalog_code?: string | null;
    wilaya_code?: string | null;
    license_number?: string | null;
    years_experience?: string | null;
    phone_public?: string | null;
    bio?: string | null;
    address?: string | null;
    city?: string | null;
    latitude?: number | string | null;
    longitude?: number | string | null;
    is_available: boolean;
    emergency_24_7: boolean;
    is_on_duty: boolean;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
    services_count?: number;
    schedules_count?: number;
    user?: UserItem | null;
    partner_type?: PartnerTypeItem | null;
    partnerType?: PartnerTypeItem | null;
    profession?: ProfessionItem | null;
    speciality?: SpecialityItem | null;
    catalog?: CenterCatalogItem | null;
    wilaya?: WilayaItem | null;
}

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
}

interface Props {
    partners: PaginatedData<PartnerItem>;
    partnerTypes: PartnerTypeItem[];
    professions: ProfessionItem[];
    specialities: SpecialityItem[];
    wilayas: WilayaItem[];
    catalogs: CenterCatalogItem[];
    filters: {
        search: string;
        partner_type: string;
        status: string;
        wilaya: string;
        per_page: number;
    };
}

export default function PartnersListPage({
    partners,
    partnerTypes,
    professions,
    specialities,
    wilayas,
    catalogs,
    filters,
}: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [partnerTypeFilter, setPartnerTypeFilter] = useState(
        filters.partner_type || 'all'
    );
    const [statusFilter, setStatusFilter] = useState(filters.status || 'all');
    const [wilayaFilter, setWilayaFilter] = useState(filters.wilaya || 'all');
    const [isPending, startTransition] = useTransition();

    // Quick Edit Partner Modal State
    const [editModalOpen, setEditModalOpen] = useState(false);
    const [editingPartner, setEditingPartner] = useState<PartnerItem | null>(
        null
    );
    const [form, setForm] = useState({
        name: '',
        partner_type_code: '',
        profession_code: '',
        speciality_code: '',
        custom_speciality: '',
        center_catalog_code: '',
        wilaya_code: '',
        license_number: '',
        years_experience: '',
        phone_public: '',
        city: '',
        address: '',
        bio: '',
        is_active: true,
        is_available: true,
        emergency_24_7: false,
    });
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [togglingId, setTogglingId] = useState<number | null>(null);

    // Filter Trigger
    const applyFilters = (
        newSearch: string,
        newType: string,
        newStatus: string,
        newWilaya: string
    ) => {
        startTransition(() => {
            router.get(
                partnersIndex.url(),
                {
                    search: newSearch || undefined,
                    partner_type: newType !== 'all' ? newType : undefined,
                    status: newStatus !== 'all' ? newStatus : undefined,
                    wilaya: newWilaya !== 'all' ? newWilaya : undefined,
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                }
            );
        });
    };

    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        applyFilters(search, partnerTypeFilter, statusFilter, wilayaFilter);
    };

    // Filter available professions & specialities based on selected partner_type
    const filteredProfessions = form.partner_type_code
        ? professions.filter(
            (p) =>
                !p.partner_type_code ||
                p.partner_type_code === form.partner_type_code
        )
        : professions;

    const filteredSpecialities = form.profession_code
        ? specialities.filter(
            (s) =>
                !s.profession_code || s.profession_code === form.profession_code
        )
        : specialities;

    // Open Edit Modal
    const openEditModal = (partner: PartnerItem) => {
        setEditingPartner(partner);
        setForm({
            name: partner.name || '',
            partner_type_code: partner.partner_type_code || '',
            profession_code: partner.profession_code || '',
            speciality_code: partner.speciality_code || '',
            custom_speciality: partner.custom_speciality || '',
            center_catalog_code: partner.center_catalog_code || '',
            wilaya_code: partner.wilaya_code || '',
            license_number: partner.license_number || '',
            years_experience: partner.years_experience || '',
            phone_public: partner.phone_public || '',
            city: partner.city || '',
            address: partner.address || '',
            bio: partner.bio || '',
            is_active: Boolean(partner.is_active),
            is_available: Boolean(partner.is_available),
            emergency_24_7: Boolean(partner.emergency_24_7),
        });
        setErrors({});
        setEditModalOpen(true);
    };

    // Submit Edit
    const handleFormSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!editingPartner) return;

        setIsSubmitting(true);
        setErrors({});

        try {
            const res = await fetch(
                updatePartnerAction.url(editingPartner.id),
                {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN':
                            (
                                document.querySelector(
                                    'meta[name="csrf-token"]'
                                ) as HTMLMetaElement
                            )?.content || '',
                    },
                    body: JSON.stringify(form),
                }
            );

            const data = await res.json();

            if (!res.ok) {
                if (res.status === 422 && data.errors) {
                    const formatted: Record<string, string> = {};
                    for (const [key, msgs] of Object.entries(data.errors)) {
                        formatted[key] = (msgs as string[])[0];
                    }
                    setErrors(formatted);
                } else {
                    toast.error(data.message || 'Error updating partner');
                }
                return;
            }

            toast.success('Partner details updated');
            setEditModalOpen(false);
            router.reload({ only: ['partners'] });
        } catch {
            toast.error('Failed to submit form');
        } finally {
            setIsSubmitting(false);
        }
    };

    // Fast Toggle Status (Approve / Deactivate) without refresh
    const handleToggleStatus = async (partner: PartnerItem) => {
        setTogglingId(partner.id);
        try {
            const res = await fetch(
                toggleStatusAction.url(partner.id),
                {
                    method: 'PATCH',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN':
                            (
                                document.querySelector(
                                    'meta[name="csrf-token"]'
                                ) as HTMLMetaElement
                            )?.content || '',
                    },
                }
            );

            const data = await res.json();
            if (res.ok) {
                toast.success(data.message || 'Status updated');
                router.reload({ only: ['partners'] });
            } else {
                toast.error(data.message || 'Failed to toggle status');
            }
        } catch {
            toast.error('Network error');
        } finally {
            setTogglingId(null);
        }
    };

    // Delete Partner
    const handleDeletePartner = async (partner: PartnerItem) => {
        if (
            !confirm(
                `Are you sure you want to delete partner "${partner.name || partner.user?.full_name}"?`
            )
        ) {
            return;
        }

        try {
            const res = await fetch(
                destroyPartnerAction.url(partner.id),
                {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN':
                            (
                                document.querySelector(
                                    'meta[name="csrf-token"]'
                                ) as HTMLMetaElement
                            )?.content || '',
                    },
                }
            );

            if (res.ok) {
                toast.success('Partner deleted successfully');
                router.reload({ only: ['partners'] });
            } else {
                toast.error('Failed to delete partner');
            }
        } catch {
            toast.error('Network error');
        }
    };

    return (
        <>
            <Head title="Partners Directory" />

            <div className="flex min-h-screen flex-1 flex-col gap-6 p-4 md:p-8">
                {/* Header */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <div className="flex items-center gap-2.5">
                            <h1 className="text-2xl font-bold tracking-tight text-foreground md:text-3xl">
                                Partners Management
                            </h1>
                            <Badge
                                variant="secondary"
                                className="font-mono text-xs"
                            >
                                {partners.total} registered
                            </Badge>
                        </div>
                        <p className="text-muted-foreground mt-1 text-sm">
                            Inspect, approve, verify and edit healthcare providers,
                            clinics, doctors, and pharmacies.
                        </p>
                    </div>
                </div>

                {/* Filter Controls Card */}
                <Card className="border-border/60 shadow-xs">
                    <CardContent className="p-4">
                        <div className="flex flex-col gap-3">
                            <form
                                onSubmit={handleSearchSubmit}
                                className="relative w-full"
                            >
                                <Search className="text-muted-foreground absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                                <Input
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Search by name, email, phone, city, license..."
                                    className="pr-8 pl-9"
                                />
                                {search && (
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setSearch('');
                                            applyFilters(
                                                '',
                                                partnerTypeFilter,
                                                statusFilter,
                                                wilayaFilter
                                            );
                                        }}
                                        className="text-muted-foreground hover:text-foreground absolute top-1/2 right-2.5 -translate-y-1/2"
                                    >
                                        <X className="size-4" />
                                    </button>
                                )}
                            </form>

                            <div className="flex flex-wrap items-center gap-2.5">
                                {/* Partner Type filter */}
                                <Select
                                    value={partnerTypeFilter}
                                    onValueChange={(val) => {
                                        setPartnerTypeFilter(val);
                                        applyFilters(
                                            search,
                                            val,
                                            statusFilter,
                                            wilayaFilter
                                        );
                                    }}
                                >
                                    <SelectTrigger className="w-[160px]">
                                        <SelectValue placeholder="All Partner Types" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">
                                            All Partner Types
                                        </SelectItem>
                                        {partnerTypes.map((pt) => (
                                            <SelectItem
                                                key={pt.code}
                                                value={pt.code}
                                            >
                                                {pt.en}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>

                                {/* Status Filter */}
                                <Select
                                    value={statusFilter}
                                    onValueChange={(val) => {
                                        setStatusFilter(val);
                                        applyFilters(
                                            search,
                                            partnerTypeFilter,
                                            val,
                                            wilayaFilter
                                        );
                                    }}
                                >
                                    <SelectTrigger className="w-[140px]">
                                        <SelectValue placeholder="All Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">
                                            All Statuses
                                        </SelectItem>
                                        <SelectItem value="active">
                                            Active / Approved
                                        </SelectItem>
                                        <SelectItem value="inactive">
                                            Pending / Inactive
                                        </SelectItem>
                                    </SelectContent>
                                </Select>

                                {/* Wilaya Filter */}
                                <Select
                                    value={wilayaFilter}
                                    onValueChange={(val) => {
                                        setWilayaFilter(val);
                                        applyFilters(
                                            search,
                                            partnerTypeFilter,
                                            statusFilter,
                                            val
                                        );
                                    }}
                                >
                                    <SelectTrigger className="w-[150px]">
                                        <SelectValue placeholder="All Wilayas" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">
                                            All Wilayas
                                        </SelectItem>
                                        {wilayas.map((w) => (
                                            <SelectItem
                                                key={w.code}
                                                value={w.code}
                                            >
                                                {w.number ? `${w.number} - ` : ''}
                                                {w.en || w.fr || w.ar}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>

                                {isPending && (
                                    <Loader2 className="text-muted-foreground size-4 animate-spin" />
                                )}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Partners List / Table View */}
                <div className="overflow-hidden rounded-xl border border-border/60 bg-card shadow-xs">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="border-b border-border/60 bg-muted/40 font-medium text-muted-foreground">
                                <tr>
                                    <th className="py-3.5 pr-4 pl-6 font-semibold">Partner / Provider</th>
                                    <th className="px-4 py-3.5 font-semibold">Type & Profession</th>
                                    <th className="px-4 py-3.5 font-semibold">Location</th>
                                    <th className="px-4 py-3.5 font-semibold">Phone & License</th>
                                    <th className="px-4 py-3.5 font-semibold">Status</th>
                                    <th className="py-3.5 pr-6 pl-4 text-right font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border/60">
                                {partners.data.map((partner) => {
                                    const displayName =
                                        partner.name ||
                                        partner.user?.full_name ||
                                        partner.user?.name ||
                                        'Unnamed Partner';
                                    const pType = partner.partner_type || partner.partnerType;
                                    const professionHex = partner.profession?.hex || '#0284C7';

                                    return (
                                        <tr
                                            key={partner.id}
                                            className="transition-colors hover:bg-muted/30"
                                        >
                                            {/* Partner Name & Subtitle */}
                                            <td className="py-3.5 pr-4 pl-6">
                                                <div className="flex items-center gap-3">
                                                    <div
                                                        className="size-3 shrink-0 rounded-full ring-2 ring-black/5 dark:ring-white/10"
                                                        style={{ backgroundColor: professionHex }}
                                                        title={`Brand color: ${professionHex}`}
                                                    />
                                                    <div className="space-y-0.5">
                                                        <Link
                                                            href={showPartnerAction.url(partner.id)}
                                                            className="font-semibold text-foreground text-sm hover:underline"
                                                        >
                                                            {displayName}
                                                        </Link>
                                                        <p className="text-[11px] text-muted-foreground">
                                                            User: {partner.user?.email || '—'}
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>

                                            {/* Type & Profession / Speciality */}
                                            <td className="px-4 py-3.5">
                                                <div className="space-y-1">
                                                    <div className="flex items-center gap-1.5">
                                                        <Badge variant="secondary" className="capitalize text-[11px]">
                                                            {pType?.en || partner.partner_type_code || 'Provider'}
                                                        </Badge>
                                                    </div>
                                                    <p className="text-muted-foreground text-xs">
                                                        {partner.profession?.en || partner.catalog?.en || 'General'}
                                                        {(partner.speciality?.en || partner.custom_speciality) && (
                                                            <span> • {partner.speciality?.en || partner.custom_speciality}</span>
                                                        )}
                                                    </p>
                                                </div>
                                            </td>

                                            {/* Location */}
                                            <td className="px-4 py-3.5">
                                                <div className="space-y-0.5">
                                                    <span className="flex items-center gap-1 font-medium text-foreground">
                                                        <MapPin className="size-3.5 text-muted-foreground shrink-0" />
                                                        {partner.city || partner.wilaya?.en || '—'}
                                                    </span>
                                                    {partner.wilaya?.en && partner.city && (
                                                        <p className="pl-4.5 text-[11px] text-muted-foreground">
                                                            {partner.wilaya.en} {partner.wilaya.number ? `(${partner.wilaya.number})` : ''}
                                                        </p>
                                                    )}
                                                </div>
                                            </td>

                                            {/* Phone & License */}
                                            <td className="px-4 py-3.5">
                                                <div className="space-y-0.5 font-mono text-xs">
                                                    <span className="flex items-center gap-1 text-foreground">
                                                        <Phone className="size-3.5 text-muted-foreground shrink-0" />
                                                        {partner.phone_public || partner.user?.phone_number || '—'}
                                                    </span>
                                                    {partner.license_number && (
                                                        <p className="pl-4.5 text-[11px] text-muted-foreground">
                                                            Lic: {partner.license_number}
                                                        </p>
                                                    )}
                                                </div>
                                            </td>

                                            {/* Status Badge */}
                                            <td className="px-4 py-3.5">
                                                <Badge
                                                    variant={partner.is_active ? 'default' : 'destructive'}
                                                    className="text-[11px]"
                                                >
                                                    {partner.is_active ? 'Active / Approved' : 'Pending Approval'}
                                                </Badge>
                                            </td>

                                            {/* Actions */}
                                            <td className="py-3.5 pr-6 pl-4 text-right">
                                                <div className="flex items-center justify-end gap-1">
                                                    <Button
                                                        asChild
                                                        variant="ghost"
                                                        size="icon"
                                                        className="size-8 text-muted-foreground hover:text-foreground"
                                                        title="Full Profile"
                                                    >
                                                        <Link href={showPartnerAction.url(partner.id)}>
                                                            <Eye className="size-4" />
                                                        </Link>
                                                    </Button>

                                                    {/* Quick Approval / Deactivate toggle */}
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        disabled={togglingId === partner.id}
                                                        onClick={() => handleToggleStatus(partner)}
                                                        className={`size-8 ${!partner.is_active
                                                            ? 'text-amber-500 hover:bg-amber-500/10'
                                                            : 'text-emerald-500 hover:bg-emerald-500/10'
                                                            }`}
                                                        title={
                                                            !partner.is_active
                                                                ? 'Deactivate Partner'
                                                                : 'Approve / Activate Partner'
                                                        }
                                                    >
                                                        {togglingId === partner.id ? (
                                                            <Loader2 className="size-4 animate-spin" />
                                                        ) : !partner.is_active ? (
                                                            <UserX className="size-4" />
                                                        ) : (
                                                            <UserCheck className="size-4" />
                                                        )}
                                                    </Button>

                                                    {/* Quick Edit Modal */}
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        onClick={() => openEditModal(partner)}
                                                        className="size-8 text-muted-foreground hover:text-foreground"
                                                        title="Quick Edit"
                                                    >
                                                        <Edit3 className="size-4" />
                                                    </Button>

                                                    {/* Delete Partner */}
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        onClick={() => handleDeletePartner(partner)}
                                                        className="size-8 text-destructive hover:bg-destructive/10"
                                                        title="Delete Partner"
                                                    >
                                                        <Trash2 className="size-4" />
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })}

                                {partners.data.length === 0 && (
                                    <tr>
                                        <td colSpan={6} className="py-12 text-center">
                                            <div className="flex flex-col items-center justify-center">
                                                <Sparkles className="mb-2 size-8 stroke-1 text-muted-foreground" />
                                                <p className="font-semibold text-sm text-foreground">No partners found</p>
                                                <p className="mt-0.5 text-xs text-muted-foreground">
                                                    No partners matched your filter criteria.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Pagination */}
                {partners.last_page > 1 && (
                    <div className="flex flex-col items-center justify-between gap-4 border-t border-border/50 pt-4 sm:flex-row">
                        <p className="text-muted-foreground text-xs">
                            Showing{' '}
                            <span className="font-medium text-foreground">
                                {partners.from ?? 0}
                            </span>{' '}
                            to{' '}
                            <span className="font-medium text-foreground">
                                {partners.to ?? 0}
                            </span>{' '}
                            of{' '}
                            <span className="font-medium text-foreground">
                                {partners.total}
                            </span>{' '}
                            partners
                        </p>

                        <div className="flex items-center gap-1">
                            {partners.links.map((link, idx) => {
                                const isPrevious = link.label.includes('Previous');
                                const isNext = link.label.includes('Next');

                                if (isPrevious) {
                                    return (
                                        <Button
                                            key={idx}
                                            variant="outline"
                                            size="sm"
                                            disabled={!link.url}
                                            onClick={() =>
                                                link.url &&
                                                router.get(
                                                    link.url,
                                                    {},
                                                    {
                                                        preserveState: true,
                                                        preserveScroll: true,
                                                    }
                                                )
                                            }
                                            className="h-8 gap-1 px-2.5 text-xs"
                                        >
                                            <ChevronLeft className="size-3.5" />
                                            <span>Prev</span>
                                        </Button>
                                    );
                                }

                                if (isNext) {
                                    return (
                                        <Button
                                            key={idx}
                                            variant="outline"
                                            size="sm"
                                            disabled={!link.url}
                                            onClick={() =>
                                                link.url &&
                                                router.get(
                                                    link.url,
                                                    {},
                                                    {
                                                        preserveState: true,
                                                        preserveScroll: true,
                                                    }
                                                )
                                            }
                                            className="h-8 gap-1 px-2.5 text-xs"
                                        >
                                            <span>Next</span>
                                            <ChevronRight className="size-3.5" />
                                        </Button>
                                    );
                                }

                                return (
                                    <Button
                                        key={idx}
                                        variant={
                                            link.active ? 'default' : 'outline'
                                        }
                                        size="sm"
                                        disabled={!link.url}
                                        onClick={() =>
                                            link.url &&
                                            router.get(
                                                link.url,
                                                {},
                                                {
                                                    preserveState: true,
                                                    preserveScroll: true,
                                                }
                                            )
                                        }
                                        className="size-8 p-0 text-xs"
                                    >
                                        <span
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                        />
                                    </Button>
                                );
                            })}
                        </div>
                    </div>
                )}
            </div>

            {/* Quick Edit Dialog */}
            <Dialog open={editModalOpen} onOpenChange={setEditModalOpen}>
                <DialogContent className="max-h-[85vh] overflow-y-auto sm:max-w-xl">
                    <DialogHeader>
                        <DialogTitle>
                            Edit Partner: {editingPartner?.name || editingPartner?.user?.name}
                        </DialogTitle>
                        <DialogDescription>
                            Update provider credentials, types, license, and public details.
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handleFormSubmit} className="space-y-4 pt-2">
                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-1">
                                <Label htmlFor="form_name">Public Display Name</Label>
                                <Input
                                    id="form_name"
                                    value={form.name}
                                    onChange={(e) =>
                                        setForm({ ...form, name: e.target.value })
                                    }
                                    placeholder="Dr. John Doe / Care Clinic"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="space-y-1">
                                <Label htmlFor="form_partner_type">Partner Type</Label>
                                <Select
                                    value={form.partner_type_code}
                                    onValueChange={(val) =>
                                        setForm({
                                            ...form,
                                            partner_type_code: val,
                                            profession_code: '',
                                            speciality_code: '',
                                        })
                                    }
                                >
                                    <SelectTrigger id="form_partner_type">
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {partnerTypes.map((pt) => (
                                            <SelectItem
                                                key={pt.code}
                                                value={pt.code}
                                            >
                                                {pt.en}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.partner_type_code} />
                            </div>
                        </div>

                        {/* Profession & Speciality */}
                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-1">
                                <Label htmlFor="form_prof">Profession</Label>
                                <Select
                                    value={form.profession_code}
                                    onValueChange={(val) =>
                                        setForm({
                                            ...form,
                                            profession_code: val,
                                            speciality_code: '',
                                        })
                                    }
                                >
                                    <SelectTrigger id="form_prof">
                                        <SelectValue placeholder="Select profession" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {filteredProfessions.map((p) => (
                                            <SelectItem
                                                key={p.code}
                                                value={p.code}
                                            >
                                                {p.en}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.profession_code} />
                            </div>

                            <div className="space-y-1">
                                <Label htmlFor="form_spec">Speciality</Label>
                                <Select
                                    value={form.speciality_code}
                                    onValueChange={(val) =>
                                        setForm({
                                            ...form,
                                            speciality_code: val,
                                        })
                                    }
                                >
                                    <SelectTrigger id="form_spec">
                                        <SelectValue placeholder="Select speciality" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {filteredSpecialities.map((s) => (
                                            <SelectItem
                                                key={s.code}
                                                value={s.code}
                                            >
                                                {s.en}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.speciality_code} />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-1">
                                <Label htmlFor="form_license">License Number</Label>
                                <Input
                                    id="form_license"
                                    value={form.license_number}
                                    onChange={(e) =>
                                        setForm({
                                            ...form,
                                            license_number: e.target.value,
                                        })
                                    }
                                    placeholder="e.g. MED-88231"
                                />
                                <InputError message={errors.license_number} />
                            </div>

                            <div className="space-y-1">
                                <Label htmlFor="form_phone">Public Phone</Label>
                                <Input
                                    id="form_phone"
                                    value={form.phone_public}
                                    onChange={(e) =>
                                        setForm({
                                            ...form,
                                            phone_public: e.target.value,
                                        })
                                    }
                                    placeholder="+213 555 123 456"
                                />
                                <InputError message={errors.phone_public} />
                            </div>
                        </div>

                        {/* Location */}
                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-1">
                                <Label htmlFor="form_wilaya">Wilaya</Label>
                                <Select
                                    value={form.wilaya_code}
                                    onValueChange={(val) =>
                                        setForm({ ...form, wilaya_code: val })
                                    }
                                >
                                    <SelectTrigger id="form_wilaya">
                                        <SelectValue placeholder="Select Wilaya" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {wilayas.map((w) => (
                                            <SelectItem
                                                key={w.code}
                                                value={w.code}
                                            >
                                                {w.number ? `${w.number} - ` : ''}
                                                {w.en || w.fr || w.ar}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.wilaya_code} />
                            </div>

                            <div className="space-y-1">
                                <Label htmlFor="form_city">City / Municipality</Label>
                                <Input
                                    id="form_city"
                                    value={form.city}
                                    onChange={(e) =>
                                        setForm({ ...form, city: e.target.value })
                                    }
                                    placeholder="Oran Centre"
                                />
                                <InputError message={errors.city} />
                            </div>
                        </div>

                        <div className="space-y-1">
                            <Label htmlFor="form_address">Street Address</Label>
                            <Input
                                id="form_address"
                                value={form.address}
                                onChange={(e) =>
                                    setForm({ ...form, address: e.target.value })
                                }
                                placeholder="12 Boulevard de la République"
                            />
                            <InputError message={errors.address} />
                        </div>

                        {/* Toggles */}
                        <div className="grid grid-cols-3 gap-3 rounded-lg border border-border/80 bg-muted/20 p-3">
                            <label className="flex cursor-pointer items-center gap-2 text-xs">
                                <input
                                    type="checkbox"
                                    checked={form.is_active}
                                    onChange={(e) =>
                                        setForm({
                                            ...form,
                                            is_active: e.target.checked,
                                        })
                                    }
                                    className="size-4 rounded border-input"
                                />
                                <span className="font-medium">Active / Approved</span>
                            </label>

                            <label className="flex cursor-pointer items-center gap-2 text-xs">
                                <input
                                    type="checkbox"
                                    checked={form.is_available}
                                    onChange={(e) =>
                                        setForm({
                                            ...form,
                                            is_available: e.target.checked,
                                        })
                                    }
                                    className="size-4 rounded border-input"
                                />
                                <span className="font-medium">Available for Booking</span>
                            </label>

                            <label className="flex cursor-pointer items-center gap-2 text-xs">
                                <input
                                    type="checkbox"
                                    checked={form.emergency_24_7}
                                    onChange={(e) =>
                                        setForm({
                                            ...form,
                                            emergency_24_7: e.target.checked,
                                        })
                                    }
                                    className="size-4 rounded border-input"
                                />
                                <span className="font-medium">24/7 Emergency</span>
                            </label>
                        </div>

                        <DialogFooter className="pt-2">
                            <DialogClose asChild>
                                <Button type="button" variant="outline">
                                    Cancel
                                </Button>
                            </DialogClose>
                            <Button type="submit" disabled={isSubmitting}>
                                {isSubmitting && (
                                    <Loader2 className="size-4 animate-spin" />
                                )}
                                <span>Save Changes</span>
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}

PartnersListPage.layout = {
    breadcrumbs: [
        {
            title: 'Partners',
            href: partnersIndex.url(),
        },
    ],
};
