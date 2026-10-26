import { Head, Link, router, useForm } from "@inertiajs/react";
import { Loader2, Mail, Printer, RotateCcw, Save } from "lucide-react";
import { useMemo, useRef, useState } from "react";
import { useTranslation } from "react-i18next";
import QRCode from "react-qr-code";

import { FieldError } from "@admin/components/field-error";
import { Alert, AlertDescription } from "@admin/components/ui/alert";
import { Button } from "@admin/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@admin/components/ui/card";
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@admin/components/ui/dialog";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@admin/components/ui/tabs";
import { Textarea } from "@admin/components/ui/textarea";
import { toast } from "@admin/lib/toast";

import { CustomerPicker, type PickedCustomer } from "../customers/components/customer-picker";

type Props = {
    activationKey: string;
    template: string;
    values: Record<string, string>;
    qrUrl: string;
    owner: string | null;
    shortCodes: string[];
};

const SHORT_CODE_LABELS: Record<string, string> = {
    ":logo": "Your logo (image address)",
    ":websitename": "Website name",
    ":customername": "Customer's name",
    ":activationcode": "Activation key",
    ":supportemail": "Support email",
    ":supportphone": "Support phone",
    ":qrcode": "Where the QR code goes",
};

// Styles the stored template relies on (from the previous greeting letter page).
const LETTER_CSS = `
.letter .activation-code{font-size:16px;font-weight:700;color:#d9534f;margin:10px 0}
.letter .qr-code{margin-top:20px}
.letter .logo{margin-bottom:20px}
.letter .logo img{max-width:150px}
.letter h2{font-size:1.4rem;font-weight:600;margin:.5rem 0}
.letter h3{font-size:1.05rem;font-weight:600;margin:.75rem 0 .25rem}
.letter h4{font-size:1rem;font-weight:500;margin:.5rem 0}
.letter p{margin:.4rem 0}
.letter ol{list-style:decimal;padding-inline-start:1.25rem;margin:.5rem 0}
.letter a{text-decoration:underline}
@media print{.letter .d-print-none{display:none}}
`;

function fill(template: string, values: Record<string, string>) {
    return Object.entries(values).reduce((html, [code, value]) => html.split(code).join(value), template);
}

/** PNG data URL of the rendered QR (for the email attachment). */
async function qrToPng(svg: SVGSVGElement | null): Promise<string | null> {
    if (!svg) return null;
    const data = new XMLSerializer().serializeToString(svg);
    const image = new Image();
    image.src = `data:image/svg+xml;charset=utf-8,${encodeURIComponent(data)}`;
    await image.decode();
    const canvas = document.createElement("canvas");
    canvas.width = 300;
    canvas.height = 300;
    const context = canvas.getContext("2d");
    if (!context) return null;
    context.fillStyle = "#fff";
    context.fillRect(0, 0, 300, 300);
    context.drawImage(image, 15, 15, 270, 270);
    return canvas.toDataURL("image/png");
}

export default function GreetingLetter(props: Props) {
    const { t } = useTranslation();
    const qrRef = useRef<HTMLDivElement>(null);
    const [sendOpen, setSendOpen] = useState(false);
    const form = useForm({ greeting_letter: props.template });

    const [before, after] = useMemo(() => {
        const html = fill(form.data.greeting_letter, props.values);
        const index = html.indexOf(":qrcode");
        return index === -1 ? [html, ""] : [html.slice(0, index), html.slice(index + ":qrcode".length)];
    }, [form.data.greeting_letter, props.values]);

    const save = () => form.post(route("admin.update.key.greeting.letter", props.activationKey), { preserveScroll: true, onSuccess: () => form.setDefaults() });

    return (
        <>
            <Head title={t("Greeting letter")} />
            <style>{LETTER_CSS}</style>
            <div className="flex flex-1 flex-col gap-6 p-4 lg:p-6">
                <div data-print="hide" className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div className="flex flex-col gap-1">
                        <nav className="flex items-center gap-1 text-sm text-muted-foreground">
                            <Link href={route("admin.key.generations")} className="hover:text-foreground">
                                {t("Activation keys")}
                            </Link>
                            <span>/</span>
                            <span className="font-mono text-foreground">{props.activationKey}</span>
                        </nav>
                        <h1 className="text-2xl font-semibold tracking-tight">{t("Greeting letter")}</h1>
                        <p className="text-sm text-muted-foreground">
                            {t("The letter that comes with an NFC card, with its activation key and a QR code to activate it.")}
                        </p>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Button variant="outline" onClick={() => window.print()}>
                            <Printer />
                            {t("Print")}
                        </Button>
                        <Button onClick={() => setSendOpen(true)}>
                            <Mail />
                            {t("Send by email")}
                        </Button>
                    </div>
                </div>

                <div className="grid gap-6 xl:grid-cols-5">
                    <Card className="gap-4 xl:col-span-2 print:border-0 print:shadow-none">
                        <CardHeader data-print="hide">
                            <CardTitle>{t("Preview")}</CardTitle>
                            <CardDescription>
                                {props.owner ? t("Personalized for :name.").replace(":name", props.owner) : t("This key isn't linked to a customer yet.")}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="letter rounded-lg border bg-white p-6 text-sm text-neutral-900 print:border-0 print:p-0">
                                <div dangerouslySetInnerHTML={{ __html: before }} />
                                <div ref={qrRef} className="my-4 inline-block bg-white p-2">
                                    <QRCode value={props.qrUrl} size={140} level="H" />
                                </div>
                                <div dangerouslySetInnerHTML={{ __html: after }} />
                            </div>
                        </CardContent>
                    </Card>

                    <Card data-print="hide" className="gap-4 xl:col-span-3">
                        <CardHeader>
                            <CardTitle>{t("Edit letter")}</CardTitle>
                            <CardDescription>{t("One letter is shared by all NFC cards. Short codes are replaced for each card.")}</CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-4">
                            <Tabs defaultValue="html">
                                <TabsList>
                                    <TabsTrigger value="html">HTML</TabsTrigger>
                                    <TabsTrigger value="codes">{t("Short codes")}</TabsTrigger>
                                </TabsList>
                                <TabsContent value="html" className="flex flex-col gap-2">
                                    <Textarea
                                        value={form.data.greeting_letter}
                                        onChange={(event) => form.setData("greeting_letter", event.target.value)}
                                        spellCheck={false}
                                        dir="ltr"
                                        aria-invalid={!!form.errors.greeting_letter}
                                        className="min-h-[420px] bg-muted/30 font-mono text-xs leading-relaxed [field-sizing:fixed]"
                                    />
                                    <FieldError message={form.errors.greeting_letter} />
                                </TabsContent>
                                <TabsContent value="codes">
                                    <div className="grid gap-2 sm:grid-cols-2">
                                        {props.shortCodes.map((code) => (
                                            <button
                                                key={code}
                                                type="button"
                                                className="flex flex-col rounded-lg border p-3 text-start hover:bg-muted/50"
                                                onClick={() => {
                                                    navigator.clipboard?.writeText(code);
                                                    toast.success(t("Copied to clipboard"));
                                                }}
                                                title={t("Copy")}
                                            >
                                                <code className="text-sm font-semibold text-primary">{code}</code>
                                                <span className="text-xs text-muted-foreground">{t(SHORT_CODE_LABELS[code] ?? code)}</span>
                                            </button>
                                        ))}
                                    </div>
                                </TabsContent>
                            </Tabs>
                            {!form.data.greeting_letter.includes(":qrcode") && (
                                <Alert variant="info">
                                    <AlertDescription className="text-foreground">
                                        {t("Add :qrcode where the QR code should appear. Without it, the QR code is placed after the letter.")}
                                    </AlertDescription>
                                </Alert>
                            )}
                            <div className="flex justify-end gap-2">
                                <Button variant="outline" onClick={() => form.reset()} disabled={!form.isDirty || form.processing}>
                                    <RotateCcw />
                                    {t("Discard changes")}
                                </Button>
                                <Button onClick={save} disabled={!form.isDirty || form.processing}>
                                    {form.processing ? <Loader2 className="animate-spin" /> : <Save />}
                                    {t("Save letter")}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <SendDialog
                open={sendOpen}
                onOpenChange={setSendOpen}
                activationKey={props.activationKey}
                unsaved={form.isDirty}
                getQr={() => qrToPng(qrRef.current?.querySelector("svg") ?? null)}
            />
        </>
    );
}

function SendDialog({
    open,
    onOpenChange,
    activationKey,
    unsaved,
    getQr,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    activationKey: string;
    unsaved: boolean;
    getQr: () => Promise<string | null>;
}) {
    const { t } = useTranslation();
    const [mode, setMode] = useState<"customer" | "email">("customer");
    const [customer, setCustomer] = useState<PickedCustomer | null>(null);
    const [email, setEmail] = useState("");
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});

    const target = mode === "customer" ? customer?.email : email;

    const send = async () => {
        if (!target) return;
        setProcessing(true);
        const qr = await getQr().catch(() => null);
        router.post(
            route("admin.send.greeting.letter", activationKey),
            { email: target, customer_name: mode === "customer" ? customer?.name : "", qr_code: qr ?? "" },
            {
                preserveScroll: true,
                onError: setErrors,
                onSuccess: () => onOpenChange(false),
                onFinish: () => setProcessing(false),
            },
        );
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{t("Send greeting letter")}</DialogTitle>
                    <DialogDescription>{t("The saved letter is emailed with the QR code attached.")}</DialogDescription>
                </DialogHeader>
                {unsaved && (
                    <Alert variant="warning">
                        <AlertDescription className="text-foreground">{t("You have unsaved changes. Save the letter first to send the new version.")}</AlertDescription>
                    </Alert>
                )}
                <Tabs value={mode} onValueChange={(value) => setMode(value as typeof mode)}>
                    <TabsList className="w-full">
                        <TabsTrigger value="customer">{t("Existing customer")}</TabsTrigger>
                        <TabsTrigger value="email">{t("Any email address")}</TabsTrigger>
                    </TabsList>
                    <TabsContent value="customer" className="pt-2">
                        <CustomerPicker value={customer} onChange={setCustomer} />
                    </TabsContent>
                    <TabsContent value="email" className="flex flex-col gap-2 pt-2">
                        <Label htmlFor="greeting_email">{t("Email address")}</Label>
                        <Input id="greeting_email" type="email" value={email} onChange={(event) => setEmail(event.target.value)} placeholder="name@example.com" />
                    </TabsContent>
                </Tabs>
                <FieldError message={errors.email} />
                <div className="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <span className="truncate text-sm text-muted-foreground">{target ? `${t("To")} ${target}` : ""}</span>
                    <div className="flex gap-2">
                        <Button variant="outline" onClick={() => onOpenChange(false)}>
                            {t("Cancel")}
                        </Button>
                        <Button onClick={send} disabled={!target || processing}>
                            {processing && <Loader2 className="animate-spin" />}
                            {t("Send")}
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}
