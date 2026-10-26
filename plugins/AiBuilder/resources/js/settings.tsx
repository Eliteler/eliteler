/*
 | AI Business Card Builder — admin settings.
 | Built into ../../dist with `npm run plugin:build -- AiBuilder` and shipped in the zip.
 | Imports from React, Inertia, react-i18next and "@gobiz/admin" come from the
 | core admin at runtime (window.GoBizAdmin), so they are not bundled here.
 */
import { Head, router, useForm } from "@inertiajs/react";
import { Loader2, Sparkles } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import {
    Button,
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
    ConfirmDialog,
    FieldError,
    Input,
    Label,
    PageHeader,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
    Switch,
} from "@gobiz/admin";

type Option = { value: string; label: string };

type Props = {
    values: {
        aibuilder: boolean;
        provider: string;
        model: string;
        generate_image?: boolean;
    };
    savedKeys: { key_1: boolean; key_2: boolean };
    providers: (Option & { models: Option[] })[];
};

export default function AiBuilderSettings({
    values,
    savedKeys,
    providers,
}: Props) {
    const { t } = useTranslation();
    const form = useForm({ ...values, key_1: "", key_2: "" });
    const { data, setData, errors } = form;
    const [removing, setRemoving] = useState(false);
    const [removeProcessing, setRemoveProcessing] = useState(false);
    const models =
        providers.find((provider) => provider.value === data.provider)
            ?.models ?? [];

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        // Transform boolean to integer for backend submission
        form.transform((current) => ({
            ...current,
            aibuilder: current.aibuilder ? 1 : 0,
        }));
        form.post(route("admin.plugin.update.aibuilder"), {
            preserveScroll: true,
            onSuccess: () =>
                form.setData((current) => ({
                    ...current,
                    key_1: "",
                    key_2: "",
                })),
        });
    };

    const removeBackupKey = () =>
        router.post(
            route("admin.plugin.aibuilder.remove-backup-key"),
            {},
            {
                preserveScroll: true,
                onStart: () => setRemoveProcessing(true),
                onFinish: () => {
                    setRemoveProcessing(false);
                    setRemoving(false);
                },
            },
        );

    return (
        <>
            <Head title={t("AI Business Card Builder")} />
            <form
                onSubmit={submit}
                className="flex flex-1 flex-col gap-6 p-4 lg:p-6"
            >
                <PageHeader
                    title={t("AI Business Card Builder")}
                    description={t(
                        "Customers scan a paper business card and AI fills in their digital card.",
                    )}
                    breadcrumbs={[
                        {
                            label: t("Dashboard"),
                            href: route("admin.dashboard"),
                        },
                        { label: t("Plugins"), href: route("admin.plugins") },
                        { label: t("AI Business Card Builder") },
                    ]}
                />

                <div className="grid max-w-3xl gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Sparkles className="size-5 text-primary" />
                                {t("Availability")}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-5">
                            <div className="flex items-start justify-between gap-4">
                                <div className="flex flex-col gap-1">
                                    <Label htmlFor="aibuilder">
                                        {t("Turn on the AI card builder")}
                                    </Label>
                                    <p className="text-sm text-muted-foreground">
                                        {t(
                                            "Customers see the option to create a card from a photo of their paper card.",
                                        )}
                                    </p>
                                </div>
                                <Switch
                                    id="aibuilder"
                                    checked={data.aibuilder}
                                    onCheckedChange={(checked) =>
                                        setData("aibuilder", checked)
                                    }
                                />
                            </div>
                            <FieldError message={errors.aibuilder} />
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t("AI provider")}</CardTitle>
                            <CardDescription>
                                {t(
                                    "The service and model that read the card. Faster models cost less; newer models are more accurate.",
                                )}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-5 sm:grid-cols-2">
                            <div className="flex flex-col gap-2">
                                <Label htmlFor="provider">
                                    {t("Provider")}
                                </Label>
                                <Select
                                    value={data.provider}
                                    onValueChange={(value) =>
                                        setData((current) => ({
                                            ...current,
                                            provider: value,
                                            model:
                                                providers.find(
                                                    (provider) =>
                                                        provider.value ===
                                                        value,
                                                )?.models[0]?.value ?? "",
                                        }))
                                    }
                                >
                                    <SelectTrigger
                                        id="provider"
                                        className="w-full"
                                        aria-invalid={!!errors.provider}
                                    >
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {providers.map((provider) => (
                                            <SelectItem
                                                key={provider.value}
                                                value={provider.value}
                                            >
                                                {provider.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <FieldError message={errors.provider} />
                            </div>
                            <div className="flex flex-col gap-2">
                                <Label htmlFor="model">{t("Model")}</Label>
                                <Select
                                    value={data.model}
                                    onValueChange={(value) =>
                                        setData("model", value)
                                    }
                                >
                                    <SelectTrigger
                                        id="model"
                                        className="w-full"
                                        aria-invalid={!!errors.model}
                                    >
                                        <SelectValue
                                            placeholder={t("Choose a model")}
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {models.map((model) => (
                                            <SelectItem
                                                key={model.value}
                                                value={model.value}
                                            >
                                                {model.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <FieldError message={errors.model} />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t("API keys")}</CardTitle>
                            <CardDescription>
                                {t(
                                    "The backup key is used when the primary key fails or runs out of credits.",
                                )}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-5">
                            {(["key_1", "key_2"] as const).map((key) => (
                                <div key={key} className="flex flex-col gap-2">
                                    <div className="flex items-center justify-between gap-2">
                                        <Label htmlFor={key}>
                                            {key === "key_1"
                                                ? t("Primary API key")
                                                : t(
                                                      "Backup API key (optional)",
                                                  )}
                                        </Label>
                                        {key === "key_2" && savedKeys.key_2 && (
                                            <Button
                                                type="button"
                                                variant="secondary"
                                                size="sm"
                                                onClick={() =>
                                                    setRemoving(true)
                                                }
                                            >
                                                {t("Remove")}
                                            </Button>
                                        )}
                                    </div>
                                    <Input
                                        id={key}
                                        type="password"
                                        autoComplete="off"
                                        value={data[key]}
                                        onChange={(event) =>
                                            setData(key, event.target.value)
                                        }
                                        placeholder={
                                            savedKeys[key]
                                                ? t(
                                                      "Saved. Leave blank to keep it.",
                                                  )
                                                : "sk-..."
                                        }
                                        className="font-mono text-sm"
                                        aria-invalid={!!errors[key]}
                                    />
                                    <FieldError message={errors[key]} />
                                </div>
                            ))}
                        </CardContent>
                    </Card>
                </div>

                <div className="sticky bottom-0 -mx-4 mt-auto flex justify-end border-t bg-background/95 px-4 py-3 backdrop-blur lg:-mx-6 lg:px-6">
                    <Button
                        type="submit"
                        disabled={form.processing || !form.isDirty}
                    >
                        {form.processing && (
                            <Loader2 className="animate-spin" />
                        )}
                        {t("Save changes")}
                    </Button>
                </div>
            </form>

            <ConfirmDialog
                open={removing}
                onOpenChange={setRemoving}
                title={t("Remove the backup API key?")}
                description={t("Only the primary key will be used.")}
                confirmLabel={t("Remove")}
                destructive
                processing={removeProcessing}
                onConfirm={removeBackupKey}
            />
        </>
    );
}
