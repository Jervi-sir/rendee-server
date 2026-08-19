import { useState, useTransition } from 'react';
import { Head, router } from '@inertiajs/react';
import {
    ChevronLeft,
    ChevronRight,
    Edit3,
    Layers,
    ListFilter,
    Loader2,
    Plus,
    Search,
    Sparkles,
    Trash2,
    X,
} from 'lucide-react';
import { toast } from 'sonner';

import {
    destroy as destroyProfessionAction,
    destroySpeciality as destroySpecialityAction,
    specialities as getSpecialitiesAction,
    store as storeProfessionAction,
    storeSpeciality as storeSpecialityAction,
    update as updateProfessionAction,
} from '@/actions/App/Http/Controllers/Admin/Catalogs/ProfessionController';
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
import { index as professionsIndex } from '@/routes/admin/catalogs/professions';

export interface PartnerType {
    code: string;
    en: string;
    fr?: string | null;
    ar?: string | null;
}

export interface SpecialityItem {
    code: string;
    profession_code?: string | null;
    en: string;
    fr?: string | null;
    ar?: string | null;
    created_at?: string;
    updated_at?: string;
}

export interface ProfessionItem {
    code: string;
    partner_type_code?: string | null;
    en: string;
    fr?: string | null;
    ar?: string | null;
    hex: string;
    specialities_count?: number;
    partner_type?: PartnerType | null;
    created_at?: string;
    updated_at?: string;
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
    professions: PaginatedData<ProfessionItem>;
    partnerTypes: PartnerType[];
    filters: {
        partner_type: string;
        search: string;
        per_page: number;
    };
}

export default function ProfessionsPage({
    professions,
    partnerTypes,
    filters,
}: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [partnerTypeFilter, setPartnerTypeFilter] = useState(
        filters.partner_type || 'all'
    );
    const [isPending, startTransition] = useTransition();

    // Modal state for Profession Upsert
    const [professionModalOpen, setProfessionModalOpen] = useState(false);
    const [editingProfession, setEditingProfession] =
        useState<ProfessionItem | null>(null);
    const [profForm, setProfForm] = useState({
        code: '',
        partner_type_code: '',
        en: '',
        fr: '',
        ar: '',
        hex: '#3B82F6',
    });
    const [profErrors, setProfErrors] = useState<Record<string, string>>({});
    const [isSubmittingProfession, setIsSubmittingProfession] = useState(false);

    // Modal state for Specialities Inspection & Upsert
    const [specialitiesModalOpen, setSpecialitiesModalOpen] = useState(false);
    const [activeProfession, setActiveProfession] =
        useState<ProfessionItem | null>(null);
    const [specialitiesList, setSpecialitiesList] = useState<SpecialityItem[]>(
        []
    );
    const [isLoadingSpecialities, setIsLoadingSpecialities] = useState(false);
    const [specForm, setSpecForm] = useState({
        code: '',
        en: '',
        fr: '',
        ar: '',
    });
    const [specErrors, setSpecErrors] = useState<Record<string, string>>({});
    const [isSubmittingSpeciality, setIsSubmittingSpeciality] = useState(false);

    // Filter handling
    const applyFilters = (newSearch: string, newPartnerType: string) => {
        startTransition(() => {
            router.get(
                professionsIndex.url(),
                {
                    search: newSearch || undefined,
                    partner_type:
                        newPartnerType !== 'all' ? newPartnerType : undefined,
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
        applyFilters(search, partnerTypeFilter);
    };

    const handlePartnerTypeChange = (val: string) => {
        setPartnerTypeFilter(val);
        applyFilters(search, val);
    };

    // Open Profession Modal
    const openCreateProfession = () => {
        setEditingProfession(null);
        setProfForm({
            code: '',
            partner_type_code:
                partnerTypes.length > 0 ? partnerTypes[0].code : '',
            en: '',
            fr: '',
            ar: '',
            hex: '#3B82F6',
        });
        setProfErrors({});
        setProfessionModalOpen(true);
    };

    const openEditProfession = (profession: ProfessionItem) => {
        setEditingProfession(profession);
        setProfForm({
            code: profession.code,
            partner_type_code: profession.partner_type_code || '',
            en: profession.en || '',
            fr: profession.fr || '',
            ar: profession.ar || '',
            hex: profession.hex || '#3B82F6',
        });
        setProfErrors({});
        setProfessionModalOpen(true);
    };

    // Submit Profession Upsert
    const handleProfessionSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmittingProfession(true);
        setProfErrors({});

        try {
            const url = editingProfession
                ? updateProfessionAction.url(editingProfession.code)
                : storeProfessionAction.url();

            const method = editingProfession ? 'PUT' : 'POST';

            const res = await fetch(url, {
                method,
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
                body: JSON.stringify(profForm),
            });

            const data = await res.json();

            if (!res.ok) {
                if (res.status === 422 && data.errors) {
                    const formattedErrors: Record<string, string> = {};
                    for (const [key, msgs] of Object.entries(data.errors)) {
                        formattedErrors[key] = (msgs as string[])[0];
                    }
                    setProfErrors(formattedErrors);
                } else {
                    toast.error(data.message || 'An error occurred.');
                }
                return;
            }

            toast.success(
                editingProfession
                    ? 'Profession updated successfully'
                    : 'Profession created successfully'
            );
            setProfessionModalOpen(false);
            router.reload({ only: ['professions'] });
        } catch (err) {
            toast.error('Failed to submit request.');
        } finally {
            setIsSubmittingProfession(false);
        }
    };

    // Delete Profession
    const handleDeleteProfession = async (profession: ProfessionItem) => {
        if (
            !confirm(
                `Are you sure you want to delete profession "${profession.en}" (${profession.code})?`
            )
        ) {
            return;
        }

        try {
            const res = await fetch(
                destroyProfessionAction.url(profession.code),
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
                toast.success('Profession deleted successfully');
                router.reload({ only: ['professions'] });
            } else {
                const data = await res.json();
                toast.error(data.message || 'Could not delete profession');
            }
        } catch {
            toast.error('Failed to delete profession');
        }
    };

    // Open Specialities Inspection & Upsert Modal
    const openSpecialitiesModal = async (profession: ProfessionItem) => {
        setActiveProfession(profession);
        setSpecialitiesModalOpen(true);
        setIsLoadingSpecialities(true);
        setSpecForm({ code: '', en: '', fr: '', ar: '' });
        setSpecErrors({});

        try {
            const res = await fetch(getSpecialitiesAction.url(profession.code), {
                headers: {
                    Accept: 'application/json',
                },
            });
            const data = await res.json();
            setSpecialitiesList(data.specialities || []);
        } catch {
            toast.error('Failed to load specialities');
        } finally {
            setIsLoadingSpecialities(false);
        }
    };

    // Submit Speciality Upsert
    const handleSpecialitySubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!activeProfession) return;

        setIsSubmittingSpeciality(true);
        setSpecErrors({});

        try {
            const res = await fetch(
                storeSpecialityAction.url(activeProfession.code),
                {
                    method: 'POST',
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
                    body: JSON.stringify(specForm),
                }
            );

            const data = await res.json();

            if (!res.ok) {
                if (res.status === 422 && data.errors) {
                    const formatted: Record<string, string> = {};
                    for (const [key, msgs] of Object.entries(data.errors)) {
                        formatted[key] = (msgs as string[])[0];
                    }
                    setSpecErrors(formatted);
                } else {
                    toast.error(data.message || 'Error saving speciality');
                }
                return;
            }

            toast.success('Speciality saved');
            setSpecForm({ code: '', en: '', fr: '', ar: '' });

            // Refresh list
            const listRes = await fetch(
                getSpecialitiesAction.url(activeProfession.code),
                {
                    headers: { Accept: 'application/json' },
                }
            );
            const listData = await listRes.json();
            setSpecialitiesList(listData.specialities || []);

            // Also reload background counts
            router.reload({ only: ['professions'] });
        } catch {
            toast.error('Failed to save speciality');
        } finally {
            setIsSubmittingSpeciality(false);
        }
    };

    // Delete Speciality
    const handleDeleteSpeciality = async (speciality: SpecialityItem) => {
        if (
            !confirm(
                `Delete speciality "${speciality.en}" (${speciality.code})?`
            )
        ) {
            return;
        }

        try {
            const res = await fetch(
                destroySpecialityAction.url(speciality.code),
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
                toast.success('Speciality deleted');
                setSpecialitiesList((prev) =>
                    prev.filter((s) => s.code !== speciality.code)
                );
                router.reload({ only: ['professions'] });
            } else {
                toast.error('Could not delete speciality');
            }
        } catch {
            toast.error('Failed to delete speciality');
        }
    };

    return (
        <>
            <Head title="Professions Catalog" />

            <div className="flex min-h-screen flex-1 flex-col gap-6 p-4 md:p-8">
                {/* Header section */}
                <div className="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-bold tracking-tight text-foreground md:text-3xl">
                                Professions Catalog
                            </h1>
                            <Badge
                                variant="secondary"
                                className="font-mono text-xs"
                            >
                                {professions.total} total
                            </Badge>
                        </div>
                        <p className="text-muted-foreground mt-1 text-sm">
                            Manage medical & healthcare professions, partner
                            type mappings, and associated specialities.
                        </p>
                    </div>

                    <Button
                        onClick={openCreateProfession}
                        className="shadow-sm transition-all"
                    >
                        <Plus className="size-4" />
                        <span>Add Profession</span>
                    </Button>
                </div>

                {/* Filters card */}
                <Card className="border-border/60 shadow-xs">
                    <CardContent className="p-4">
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            {/* Search bar */}
                            <form
                                onSubmit={handleSearchSubmit}
                                className="relative flex-1"
                            >
                                <Search className="text-muted-foreground absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                                <Input
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Search by code, EN, FR, or AR name..."
                                    className="pr-8 pl-9"
                                />
                                {search && (
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setSearch('');
                                            applyFilters(
                                                '',
                                                partnerTypeFilter
                                            );
                                        }}
                                        className="text-muted-foreground hover:text-foreground absolute top-1/2 right-2.5 -translate-y-1/2"
                                    >
                                        <X className="size-4" />
                                    </button>
                                )}
                            </form>

                            {/* Partner Type filter */}
                            <div className="flex items-center gap-2">
                                <ListFilter className="text-muted-foreground size-4 shrink-0" />
                                <Select
                                    value={partnerTypeFilter}
                                    onValueChange={handlePartnerTypeChange}
                                >
                                    <SelectTrigger className="w-[180px]">
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
                                                {pt.en} ({pt.code})
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

                {/* Professions Table Row Layout */}
                <div className="overflow-hidden rounded-xl border border-border/60 bg-card shadow-xs">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="border-b border-border/60 bg-muted/40 font-medium text-muted-foreground">
                                <tr>
                                    <th className="py-3.5 pr-4 pl-6 font-semibold">Profession</th>
                                    <th className="px-4 py-3.5 font-semibold">Partner Type</th>
                                    <th className="px-4 py-3.5 font-semibold">Translations (FR / AR)</th>
                                    <th className="px-4 py-3.5 font-semibold">Specialities</th>
                                    <th className="px-4 py-3.5 font-semibold">Hex Color</th>
                                    <th className="py-3.5 pr-6 pl-4 text-right font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border/60">
                                {professions.data.map((profession) => (
                                    <tr
                                        key={profession.code}
                                        className="transition-colors hover:bg-muted/30"
                                    >
                                        {/* Profession EN & Code */}
                                        <td className="py-3.5 pr-4 pl-6">
                                            <div className="flex items-center gap-3">
                                                <span
                                                    className="size-3.5 shrink-0 rounded-full ring-2 ring-black/5 dark:ring-white/10"
                                                    style={{ backgroundColor: profession.hex || '#3B82F6' }}
                                                    title={`Color: ${profession.hex}`}
                                                />
                                                <div className="space-y-0.5">
                                                    <span className="font-semibold text-foreground text-sm">
                                                        {profession.en}
                                                    </span>
                                                    <p className="font-mono text-[11px] text-muted-foreground">
                                                        {profession.code}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        {/* Partner Type */}
                                        <td className="px-4 py-3.5">
                                            {profession.partner_type ? (
                                                <Badge variant="secondary" className="capitalize text-[11px]">
                                                    {profession.partner_type.en}
                                                </Badge>
                                            ) : (
                                                <Badge variant="outline" className="text-muted-foreground text-[11px]">
                                                    No Type
                                                </Badge>
                                            )}
                                        </td>

                                        {/* Translations */}
                                        <td className="px-4 py-3.5">
                                            <div className="space-y-0.5 text-xs">
                                                <p className="text-muted-foreground">
                                                    <span className="font-medium text-foreground">FR:</span> {profession.fr || '—'}
                                                </p>
                                                <p className="text-muted-foreground" dir="rtl">
                                                    <span className="font-medium text-foreground">AR:</span> {profession.ar || '—'}
                                                </p>
                                            </div>
                                        </td>

                                        {/* Specialities count button */}
                                        <td className="px-4 py-3.5">
                                            <button
                                                type="button"
                                                onClick={() => openSpecialitiesModal(profession)}
                                                className="inline-flex items-center gap-1.5 font-medium text-primary hover:underline"
                                            >
                                                <Layers className="size-3.5" />
                                                <span>{profession.specialities_count || 0} Specialities</span>
                                            </button>
                                        </td>

                                        {/* Hex */}
                                        <td className="px-4 py-3.5">
                                            <div className="flex items-center gap-2 font-mono text-xs text-muted-foreground">
                                                <span>{profession.hex}</span>
                                            </div>
                                        </td>

                                        {/* Actions */}
                                        <td className="py-3.5 pr-6 pl-4 text-right">
                                            <div className="flex items-center justify-end gap-1">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() => openSpecialitiesModal(profession)}
                                                    className="h-8 gap-1 text-xs"
                                                >
                                                    <Layers className="size-3.5" />
                                                    <span>Specialities</span>
                                                </Button>

                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => openEditProfession(profession)}
                                                    className="size-8 text-muted-foreground hover:text-foreground"
                                                    title="Edit Profession"
                                                >
                                                    <Edit3 className="size-3.5" />
                                                </Button>

                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => handleDeleteProfession(profession)}
                                                    className="size-8 text-destructive hover:bg-destructive/10"
                                                    title="Delete Profession"
                                                >
                                                    <Trash2 className="size-3.5" />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}

                                {professions.data.length === 0 && (
                                    <tr>
                                        <td colSpan={6} className="py-12 text-center">
                                            <div className="flex flex-col items-center justify-center">
                                                <Sparkles className="mb-2 size-8 stroke-1 text-muted-foreground" />
                                                <p className="font-semibold text-sm text-foreground">No professions found</p>
                                                <p className="mt-0.5 text-xs text-muted-foreground">
                                                    Try adjusting your search query or partner type filter.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Pagination Controls */}
                {professions.last_page > 1 && (
                    <div className="flex flex-col items-center justify-between gap-4 border-t border-border/50 pt-4 sm:flex-row">
                        <p className="text-muted-foreground text-xs">
                            Showing{' '}
                            <span className="font-medium text-foreground">
                                {professions.from ?? 0}
                            </span>{' '}
                            to{' '}
                            <span className="font-medium text-foreground">
                                {professions.to ?? 0}
                            </span>{' '}
                            of{' '}
                            <span className="font-medium text-foreground">
                                {professions.total}
                            </span>{' '}
                            results
                        </p>

                        <div className="flex items-center gap-1">
                            {professions.links.map((link, idx) => {
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

            {/* Modal: Upsert Profession */}
            <Dialog
                open={professionModalOpen}
                onOpenChange={setProfessionModalOpen}
            >
                <DialogContent className="sm:max-w-lg">
                    <DialogHeader>
                        <DialogTitle>
                            {editingProfession
                                ? 'Edit Profession'
                                : 'Add New Profession'}
                        </DialogTitle>
                        <DialogDescription>
                            Define the profession code, localized translations,
                            and partner type mapping.
                        </DialogDescription>
                    </DialogHeader>

                    <form
                        onSubmit={handleProfessionSubmit}
                        className="space-y-4 pt-2"
                    >
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-1.5">
                                <Label htmlFor="prof_code">Code</Label>
                                <Input
                                    id="prof_code"
                                    value={profForm.code}
                                    onChange={(e) =>
                                        setProfForm({
                                            ...profForm,
                                            code: e.target.value.toLowerCase(),
                                        })
                                    }
                                    disabled={!!editingProfession}
                                    placeholder="e.g. cardiologist"
                                    required
                                />
                                <InputError message={profErrors.code} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="partner_type_code">
                                    Partner Type
                                </Label>
                                <Select
                                    value={profForm.partner_type_code || ''}
                                    onValueChange={(val) =>
                                        setProfForm({
                                            ...profForm,
                                            partner_type_code: val,
                                        })
                                    }
                                >
                                    <SelectTrigger
                                        id="partner_type_code"
                                        className="w-full"
                                    >
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {partnerTypes.map((pt) => (
                                            <SelectItem
                                                key={pt.code}
                                                value={pt.code}
                                            >
                                                {pt.en} ({pt.code})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError
                                    message={profErrors.partner_type_code}
                                />
                            </div>
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="prof_en">English Name (EN)</Label>
                            <Input
                                id="prof_en"
                                value={profForm.en}
                                onChange={(e) =>
                                    setProfForm({
                                        ...profForm,
                                        en: e.target.value,
                                    })
                                }
                                placeholder="Cardiologist"
                                required
                            />
                            <InputError message={profErrors.en} />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-1.5">
                                <Label htmlFor="prof_fr">French Name (FR)</Label>
                                <Input
                                    id="prof_fr"
                                    value={profForm.fr}
                                    onChange={(e) =>
                                        setProfForm({
                                            ...profForm,
                                            fr: e.target.value,
                                        })
                                    }
                                    placeholder="Cardiologue"
                                />
                                <InputError message={profErrors.fr} />
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="prof_ar">Arabic Name (AR)</Label>
                                <Input
                                    id="prof_ar"
                                    dir="rtl"
                                    value={profForm.ar}
                                    onChange={(e) =>
                                        setProfForm({
                                            ...profForm,
                                            ar: e.target.value,
                                        })
                                    }
                                    placeholder="طبيب قلب"
                                />
                                <InputError message={profErrors.ar} />
                            </div>
                        </div>

                        <div className="space-y-1.5">
                            <Label htmlFor="prof_hex">Brand Hex Color</Label>
                            <div className="flex items-center gap-3">
                                <input
                                    type="color"
                                    id="prof_hex_picker"
                                    value={profForm.hex}
                                    onChange={(e) =>
                                        setProfForm({
                                            ...profForm,
                                            hex: e.target.value,
                                        })
                                    }
                                    className="size-9 cursor-pointer rounded-md border border-input p-0.5"
                                />
                                <Input
                                    id="prof_hex"
                                    value={profForm.hex}
                                    onChange={(e) =>
                                        setProfForm({
                                            ...profForm,
                                            hex: e.target.value,
                                        })
                                    }
                                    placeholder="#3B82F6"
                                    className="font-mono"
                                    required
                                />
                            </div>
                            <InputError message={profErrors.hex} />
                        </div>

                        <DialogFooter className="pt-2">
                            <DialogClose asChild>
                                <Button type="button" variant="outline">
                                    Cancel
                                </Button>
                            </DialogClose>
                            <Button
                                type="submit"
                                disabled={isSubmittingProfession}
                            >
                                {isSubmittingProfession && (
                                    <Loader2 className="size-4 animate-spin" />
                                )}
                                <span>
                                    {editingProfession
                                        ? 'Save Changes'
                                        : 'Create Profession'}
                                </span>
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Modal: Specialities Viewer & Inline Upsert */}
            <Dialog
                open={specialitiesModalOpen}
                onOpenChange={setSpecialitiesModalOpen}
            >
                <DialogContent className="max-h-[85vh] overflow-y-auto sm:max-w-2xl">
                    <DialogHeader>
                        <div className="flex items-center gap-2">
                            <div
                                className="size-3 rounded-full"
                                style={{
                                    backgroundColor:
                                        activeProfession?.hex || '#3B82F6',
                                }}
                            />
                            <DialogTitle className="text-xl">
                                Specialities: {activeProfession?.en} (
                                {activeProfession?.code})
                            </DialogTitle>
                        </div>
                        <DialogDescription>
                            View and quickly add or remove sub-specialities
                            associated with this profession without leaving the
                            page.
                        </DialogDescription>
                    </DialogHeader>

                    {/* Quick Add Speciality Form */}
                    <div className="mt-2 rounded-lg border border-border/80 bg-muted/30 p-4">
                        <h4 className="mb-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                            Quick Add / Upsert Speciality
                        </h4>
                        <form
                            onSubmit={handleSpecialitySubmit}
                            className="space-y-3"
                        >
                            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div className="space-y-1">
                                    <Label
                                        htmlFor="spec_code"
                                        className="text-xs"
                                    >
                                        Code
                                    </Label>
                                    <Input
                                        id="spec_code"
                                        value={specForm.code}
                                        onChange={(e) =>
                                            setSpecForm({
                                                ...specForm,
                                                code: e.target.value.toLowerCase(),
                                            })
                                        }
                                        placeholder="e.g. pediatric_cardiology"
                                        className="h-8 text-xs"
                                        required
                                    />
                                    <InputError message={specErrors.code} />
                                </div>
                                <div className="space-y-1">
                                    <Label
                                        htmlFor="spec_en"
                                        className="text-xs"
                                    >
                                        English Name (EN)
                                    </Label>
                                    <Input
                                        id="spec_en"
                                        value={specForm.en}
                                        onChange={(e) =>
                                            setSpecForm({
                                                ...specForm,
                                                en: e.target.value,
                                            })
                                        }
                                        placeholder="Pediatric Cardiology"
                                        className="h-8 text-xs"
                                        required
                                    />
                                    <InputError message={specErrors.en} />
                                </div>
                            </div>

                            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div className="space-y-1">
                                    <Label
                                        htmlFor="spec_fr"
                                        className="text-xs"
                                    >
                                        French Name (FR)
                                    </Label>
                                    <Input
                                        id="spec_fr"
                                        value={specForm.fr}
                                        onChange={(e) =>
                                            setSpecForm({
                                                ...specForm,
                                                fr: e.target.value,
                                            })
                                        }
                                        placeholder="Cardiologie pédiatrique"
                                        className="h-8 text-xs"
                                    />
                                    <InputError message={specErrors.fr} />
                                </div>
                                <div className="space-y-1">
                                    <Label
                                        htmlFor="spec_ar"
                                        className="text-xs"
                                    >
                                        Arabic Name (AR)
                                    </Label>
                                    <Input
                                        id="spec_ar"
                                        dir="rtl"
                                        value={specForm.ar}
                                        onChange={(e) =>
                                            setSpecForm({
                                                ...specForm,
                                                ar: e.target.value,
                                            })
                                        }
                                        placeholder="طب قلب الأطفال"
                                        className="h-8 text-xs"
                                    />
                                    <InputError message={specErrors.ar} />
                                </div>
                            </div>

                            <div className="flex justify-end pt-1">
                                <Button
                                    type="submit"
                                    size="sm"
                                    disabled={isSubmittingSpeciality}
                                    className="h-8 text-xs"
                                >
                                    {isSubmittingSpeciality && (
                                        <Loader2 className="size-3.5 animate-spin" />
                                    )}
                                    <Plus className="size-3.5" />
                                    <span>Add Speciality</span>
                                </Button>
                            </div>
                        </form>
                    </div>

                    {/* Specialities List */}
                    <div className="space-y-2">
                        <div className="flex items-center justify-between">
                            <h4 className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Existing Specialities ({specialitiesList.length})
                            </h4>
                        </div>

                        {isLoadingSpecialities ? (
                            <div className="flex items-center justify-center p-8 text-muted-foreground">
                                <Loader2 className="size-6 animate-spin" />
                            </div>
                        ) : specialitiesList.length === 0 ? (
                            <div className="rounded-md border border-dashed border-border p-6 text-center text-xs text-muted-foreground">
                                No specialities registered for this profession
                                yet. Use the form above to add one.
                            </div>
                        ) : (
                            <div className="divide-y divide-border/60 rounded-md border border-border/60">
                                {specialitiesList.map((spec) => (
                                    <div
                                        key={spec.code}
                                        className="flex items-center justify-between p-3 transition-colors hover:bg-muted/30"
                                    >
                                        <div className="space-y-0.5">
                                            <div className="flex items-center gap-2">
                                                <span className="font-semibold text-foreground text-sm">
                                                    {spec.en}
                                                </span>
                                                <Badge
                                                    variant="outline"
                                                    className="font-mono text-[10px]"
                                                >
                                                    {spec.code}
                                                </Badge>
                                            </div>
                                            <div className="flex gap-4 text-xs text-muted-foreground">
                                                {spec.fr && (
                                                    <span>FR: {spec.fr}</span>
                                                )}
                                                {spec.ar && (
                                                    <span dir="rtl">
                                                        AR: {spec.ar}
                                                    </span>
                                                )}
                                            </div>
                                        </div>

                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            onClick={() =>
                                                handleDeleteSpeciality(spec)
                                            }
                                            className="size-7 text-destructive hover:bg-destructive/10"
                                            title="Delete Speciality"
                                        >
                                            <Trash2 className="size-3.5" />
                                        </Button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    <DialogFooter className="mt-2">
                        <DialogClose asChild>
                            <Button type="button" variant="outline">
                                Close
                            </Button>
                        </DialogClose>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

ProfessionsPage.layout = {
    breadcrumbs: [
        {
            title: 'Catalogs',
            href: professionsIndex.url(),
        },
        {
            title: 'Professions',
            href: professionsIndex.url(),
        },
    ],
};
