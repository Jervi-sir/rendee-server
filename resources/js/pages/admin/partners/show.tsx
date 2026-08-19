import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import {
    Activity,
    AlertCircle,
    ArrowLeft,
    Building2,
    Calendar,
    Check,
    Clock,
    Globe,
    Mail,
    MapPin,
    Phone,
    Shield,
    Sparkles,
    Stethoscope,
    Trash2,
    UserCheck,
    UserX,
} from 'lucide-react';
import { toast } from 'sonner';

import {
    destroy as destroyPartnerAction,
    toggleStatus as toggleStatusAction,
    update as updatePartnerAction,
} from '@/actions/App/Http/Controllers/Admin/Partners/PartnerController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import type {
    CenterCatalogItem,
    PartnerItem,
    PartnerTypeItem,
    ProfessionItem,
    SpecialityItem,
    WilayaItem,
} from './list';

interface ScheduleItem {
    id: number;
    day_of_week: number;
    start_time?: string | null;
    end_time?: string | null;
    is_active: boolean;
}

interface ServiceItem {
    id: number;
    name: string;
    description?: string | null;
    price?: number | string | null;
    duration_minutes?: number | null;
    is_active: boolean;
}

interface ContactItem {
    id: number;
    platform_code?: string | null;
    url?: string | null;
    platform?: {
        code: string;
        en?: string | null;
    } | null;
}

interface DetailedPartner extends PartnerItem {
    schedules: ScheduleItem[];
    services: ServiceItem[];
    contacts: ContactItem[];
}

interface Props {
    partner: DetailedPartner;
    partnerTypes: PartnerTypeItem[];
    professions: ProfessionItem[];
    specialities: SpecialityItem[];
    wilayas: WilayaItem[];
    catalogs: CenterCatalogItem[];
}

const dayNames = [
    'Sunday',
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
];

export default function PartnerShowPage({
    partner,
    partnerTypes,
    professions,
    specialities,
    wilayas,
    catalogs,
}: Props) {
    const [form, setForm] = useState({
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
        is_on_duty: Boolean(partner.is_on_duty),
    });

    const [isSaving, setIsSaving] = useState(false);

    const handleSave = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSaving(true);

        try {
            const res = await fetch(updatePartnerAction.url(partner.id), {
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
            });

            if (res.ok) {
                toast.success('Partner profile saved successfully.');
                router.reload();
            } else {
                toast.error('Failed to update partner.');
            }
        } catch {
            toast.error('Network error.');
        } finally {
            setIsSaving(false);
        }
    };

    const handleToggleStatus = async () => {
        try {
            const res = await fetch(toggleStatusAction.url(partner.id), {
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
            });
            const data = await res.json();
            if (res.ok) {
                toast.success(data.message);
                router.reload();
            }
        } catch {
            toast.error('Could not toggle status.');
        }
    };

    const handleDelete = async () => {
        if (!confirm('Are you sure you want to permanently delete this partner?')) {
            return;
        }

        try {
            await fetch(destroyPartnerAction.url(partner.id), {
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
            });
            toast.success('Partner deleted.');
            router.visit(partnersIndex.url());
        } catch {
            toast.error('Failed to delete partner.');
        }
    };

    const displayName =
        partner.name ||
        partner.user?.full_name ||
        partner.user?.name ||
        'Partner';

    return (
        <>
            <Head title={`Partner - ${displayName}`} />

            <div className="flex min-h-screen flex-1 flex-col gap-6 p-4 md:p-8">
                {/* Back button & Action Bar */}
                <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div className="flex items-center gap-3">
                        <Button asChild variant="outline" size="sm" className="h-9">
                            <Link href={partnersIndex.url()}>
                                <ArrowLeft className="size-4" />
                                <span>Back to Partners</span>
                            </Link>
                        </Button>
                        <div>
                            <div className="flex items-center gap-2">
                                <h1 className="text-xl font-bold tracking-tight text-foreground md:text-2xl">
                                    {displayName}
                                </h1>
                                <Badge
                                    variant={
                                        partner.is_active
                                            ? 'default'
                                            : 'destructive'
                                    }
                                >
                                    {partner.is_active ? 'Approved / Active' : 'Pending Approval'}
                                </Badge>
                            </div>
                            <p className="text-muted-foreground text-xs">
                                User Account: {partner.user?.email} • ID: #{partner.id}
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        <Button
                            variant={partner.is_active ? 'outline' : 'default'}
                            size="sm"
                            onClick={handleToggleStatus}
                            className="h-9 gap-1.5"
                        >
                            {partner.is_active ? (
                                <>
                                    <UserX className="size-4 text-amber-500" />
                                    <span>Deactivate</span>
                                </>
                            ) : (
                                <>
                                    <UserCheck className="size-4 text-emerald-400" />
                                    <span>Approve Partner</span>
                                </>
                            )}
                        </Button>

                        <Button
                            variant="ghost"
                            size="icon"
                            onClick={handleDelete}
                            className="size-9 text-destructive hover:bg-destructive/10"
                            title="Delete Partner"
                        >
                            <Trash2 className="size-4" />
                        </Button>
                    </div>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    {/* Left 2 Cols: Main Edit Form */}
                    <div className="space-y-6 lg:col-span-2">
                        <Card className="border-border/60 shadow-xs">
                            <CardHeader>
                                <CardTitle className="text-base">
                                    General & Professional Credentials
                                </CardTitle>
                                <CardDescription>
                                    Edit public profile and categorization for directory discovery.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={handleSave} className="space-y-4">
                                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div className="space-y-1.5">
                                            <Label htmlFor="s_name">
                                                Public Display Name
                                            </Label>
                                            <Input
                                                id="s_name"
                                                value={form.name}
                                                onChange={(e) =>
                                                    setForm({
                                                        ...form,
                                                        name: e.target.value,
                                                    })
                                                }
                                            />
                                        </div>

                                        <div className="space-y-1.5">
                                            <Label htmlFor="s_type">
                                                Partner Type
                                            </Label>
                                            <Select
                                                value={form.partner_type_code}
                                                onValueChange={(val) =>
                                                    setForm({
                                                        ...form,
                                                        partner_type_code: val,
                                                    })
                                                }
                                            >
                                                <SelectTrigger id="s_type">
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
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div className="space-y-1.5">
                                            <Label htmlFor="s_prof">Profession</Label>
                                            <Select
                                                value={form.profession_code}
                                                onValueChange={(val) =>
                                                    setForm({
                                                        ...form,
                                                        profession_code: val,
                                                    })
                                                }
                                            >
                                                <SelectTrigger id="s_prof">
                                                    <SelectValue placeholder="Select profession" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {professions.map((p) => (
                                                        <SelectItem
                                                            key={p.code}
                                                            value={p.code}
                                                        >
                                                            {p.en}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>

                                        <div className="space-y-1.5">
                                            <Label htmlFor="s_spec">Speciality</Label>
                                            <Select
                                                value={form.speciality_code}
                                                onValueChange={(val) =>
                                                    setForm({
                                                        ...form,
                                                        speciality_code: val,
                                                    })
                                                }
                                            >
                                                <SelectTrigger id="s_spec">
                                                    <SelectValue placeholder="Select speciality" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {specialities.map((s) => (
                                                        <SelectItem
                                                            key={s.code}
                                                            value={s.code}
                                                        >
                                                            {s.en}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div className="space-y-1.5">
                                            <Label htmlFor="s_license">
                                                License Number
                                            </Label>
                                            <Input
                                                id="s_license"
                                                value={form.license_number}
                                                onChange={(e) =>
                                                    setForm({
                                                        ...form,
                                                        license_number:
                                                            e.target.value,
                                                    })
                                                }
                                            />
                                        </div>

                                        <div className="space-y-1.5">
                                            <Label htmlFor="s_exp">
                                                Years Experience
                                            </Label>
                                            <Input
                                                id="s_exp"
                                                value={form.years_experience}
                                                onChange={(e) =>
                                                    setForm({
                                                        ...form,
                                                        years_experience:
                                                            e.target.value,
                                                    })
                                                }
                                            />
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div className="space-y-1.5">
                                            <Label htmlFor="s_wilaya">Wilaya</Label>
                                            <Select
                                                value={form.wilaya_code}
                                                onValueChange={(val) =>
                                                    setForm({
                                                        ...form,
                                                        wilaya_code: val,
                                                    })
                                                }
                                            >
                                                <SelectTrigger id="s_wilaya">
                                                    <SelectValue placeholder="Select Wilaya" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {wilayas.map((w) => (
                                                        <SelectItem
                                                            key={w.code}
                                                            value={w.code}
                                                        >
                                                            {w.number
                                                                ? `${w.number} - `
                                                                : ''}
                                                            {w.en || w.fr || w.ar}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>

                                        <div className="space-y-1.5">
                                            <Label htmlFor="s_city">City</Label>
                                            <Input
                                                id="s_city"
                                                value={form.city}
                                                onChange={(e) =>
                                                    setForm({
                                                        ...form,
                                                        city: e.target.value,
                                                    })
                                                }
                                            />
                                        </div>
                                    </div>

                                    <div className="space-y-1.5">
                                        <Label htmlFor="s_address">
                                            Full Street Address
                                        </Label>
                                        <Input
                                            id="s_address"
                                            value={form.address}
                                            onChange={(e) =>
                                                setForm({
                                                    ...form,
                                                    address: e.target.value,
                                                })
                                            }
                                        />
                                    </div>

                                    <div className="space-y-1.5">
                                        <Label htmlFor="s_bio">
                                            Biography / Description
                                        </Label>
                                        <textarea
                                            id="s_bio"
                                            rows={3}
                                            className="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 outline-none"
                                            value={form.bio}
                                            onChange={(e) =>
                                                setForm({
                                                    ...form,
                                                    bio: e.target.value,
                                                })
                                            }
                                        />
                                    </div>

                                    {/* Flags */}
                                    <div className="grid grid-cols-2 gap-3 rounded-lg border border-border/80 bg-muted/20 p-3 sm:grid-cols-4">
                                        <label className="flex cursor-pointer items-center gap-2 text-xs">
                                            <input
                                                type="checkbox"
                                                checked={form.is_active}
                                                onChange={(e) =>
                                                    setForm({
                                                        ...form,
                                                        is_active:
                                                            e.target.checked,
                                                    })
                                                }
                                                className="size-4 rounded border-input"
                                            />
                                            <span className="font-medium">
                                                Active
                                            </span>
                                        </label>

                                        <label className="flex cursor-pointer items-center gap-2 text-xs">
                                            <input
                                                type="checkbox"
                                                checked={form.is_available}
                                                onChange={(e) =>
                                                    setForm({
                                                        ...form,
                                                        is_available:
                                                            e.target.checked,
                                                    })
                                                }
                                                className="size-4 rounded border-input"
                                            />
                                            <span className="font-medium">
                                                Available
                                            </span>
                                        </label>

                                        <label className="flex cursor-pointer items-center gap-2 text-xs">
                                            <input
                                                type="checkbox"
                                                checked={form.emergency_24_7}
                                                onChange={(e) =>
                                                    setForm({
                                                        ...form,
                                                        emergency_24_7:
                                                            e.target.checked,
                                                    })
                                                }
                                                className="size-4 rounded border-input"
                                            />
                                            <span className="font-medium">
                                                24/7 Duty
                                            </span>
                                        </label>

                                        <label className="flex cursor-pointer items-center gap-2 text-xs">
                                            <input
                                                type="checkbox"
                                                checked={form.is_on_duty}
                                                onChange={(e) =>
                                                    setForm({
                                                        ...form,
                                                        is_on_duty:
                                                            e.target.checked,
                                                    })
                                                }
                                                className="size-4 rounded border-input"
                                            />
                                            <span className="font-medium">
                                                On Duty
                                            </span>
                                        </label>
                                    </div>

                                    <div className="flex justify-end pt-2">
                                        <Button type="submit" disabled={isSaving}>
                                            {isSaving ? 'Saving...' : 'Save Changes'}
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>

                        {/* Services List */}
                        <Card className="border-border/60 shadow-xs">
                            <CardHeader>
                                <CardTitle className="text-base">
                                    Offered Services ({partner.services.length})
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                {partner.services.length === 0 ? (
                                    <p className="text-muted-foreground text-xs">
                                        No specific services configured yet.
                                    </p>
                                ) : (
                                    <div className="divide-y divide-border/60">
                                        {partner.services.map((srv) => (
                                            <div
                                                key={srv.id}
                                                className="flex items-center justify-between py-2.5 text-xs"
                                            >
                                                <div>
                                                    <p className="font-semibold text-foreground">
                                                        {srv.name}
                                                    </p>
                                                    {srv.description && (
                                                        <p className="text-muted-foreground text-[11px]">
                                                            {srv.description}
                                                        </p>
                                                    )}
                                                </div>
                                                <div className="text-right">
                                                    <span className="font-bold text-foreground">
                                                        {srv.price ? `${srv.price} DZD` : 'Free'}
                                                    </span>
                                                    {srv.duration_minutes && (
                                                        <p className="text-muted-foreground text-[10px]">
                                                            {srv.duration_minutes} min
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Right 1 Col: User & Schedule Information */}
                    <div className="space-y-6">
                        {/* User Account Info */}
                        <Card className="border-border/60 shadow-xs">
                            <CardHeader>
                                <CardTitle className="text-base">
                                    Account & Contact Details
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 text-xs">
                                <div className="space-y-1">
                                    <span className="text-muted-foreground">
                                        Account Holder:
                                    </span>
                                    <p className="font-medium text-foreground">
                                        {partner.user?.full_name || partner.user?.name}
                                    </p>
                                </div>

                                <div className="space-y-1">
                                    <span className="text-muted-foreground">
                                        Email:
                                    </span>
                                    <p className="font-mono text-foreground">
                                        {partner.user?.email}
                                    </p>
                                </div>

                                <div className="space-y-1">
                                    <span className="text-muted-foreground">
                                        Phone Number:
                                    </span>
                                    <p className="font-mono text-foreground">
                                        {partner.phone_public ||
                                            partner.user?.phone_number ||
                                            '—'}
                                    </p>
                                </div>

                                {partner.contacts.length > 0 && (
                                    <div className="pt-2">
                                        <span className="text-muted-foreground block mb-1">
                                            Social & Web Links:
                                        </span>
                                        <div className="space-y-1">
                                            {partner.contacts.map((c) => (
                                                <div
                                                    key={c.id}
                                                    className="flex items-center gap-1.5 text-muted-foreground"
                                                >
                                                    <Globe className="size-3.5" />
                                                    <span className="capitalize">
                                                        {c.platform?.en ||
                                                            c.platform_code}
                                                        :
                                                    </span>
                                                    <span className="truncate font-mono text-foreground">
                                                        {c.url}
                                                    </span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Working Schedules */}
                        <Card className="border-border/60 shadow-xs">
                            <CardHeader>
                                <CardTitle className="text-base">
                                    Weekly Working Hours
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2 text-xs">
                                    {dayNames.map((day, idx) => {
                                        const sch = partner.schedules.find(
                                            (s) => s.day_of_week === idx
                                        );
                                        const isActive = sch && sch.is_active;

                                        return (
                                            <div
                                                key={day}
                                                className="flex items-center justify-between border-b border-border/40 py-1.5 last:border-0"
                                            >
                                                <span className="font-medium text-foreground">
                                                    {day}
                                                </span>
                                                {isActive ? (
                                                    <span className="font-mono text-muted-foreground">
                                                        {sch.start_time?.slice(0, 5)} -{' '}
                                                        {sch.end_time?.slice(0, 5)}
                                                    </span>
                                                ) : (
                                                    <span className="text-muted-foreground/60">
                                                        Off
                                                    </span>
                                                )}
                                            </div>
                                        );
                                    })}
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}

PartnerShowPage.layout = {
    breadcrumbs: [
        {
            title: 'Partners',
            href: partnersIndex.url(),
        },
        {
            title: 'Partner Details',
            href: '#',
        },
    ],
};
