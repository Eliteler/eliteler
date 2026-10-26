import { useTranslation } from "react-i18next";

import { FieldError } from "@admin/components/field-error";
import { Input } from "@admin/components/ui/input";
import { Label } from "@admin/components/ui/label";
import { Switch } from "@admin/components/ui/switch";

/**
 * Number input with an "Unlimited" switch that stores the sentinel value
 * (999 for limits, -1 for AI credits).
 */
export function LimitInput({
    id,
    label,
    value,
    onChange,
    unlimitedValue = 999,
    error,
    min = 0,
    suffix,
    hint,
}: {
    id: string;
    label: string;
    value: string;
    onChange: (value: string) => void;
    unlimitedValue?: number | null;
    error?: string;
    min?: number;
    suffix?: string;
    hint?: string;
}) {
    const { t } = useTranslation();
    const unlimited = unlimitedValue !== null && value === String(unlimitedValue);

    return (
        <div className="flex flex-col gap-2">
            <div className="flex items-center justify-between gap-2">
                <Label htmlFor={id}>{label}</Label>
                {unlimitedValue !== null && (
                    <label className="flex items-center gap-1.5 text-xs text-muted-foreground">
                        {t("Unlimited")}
                        <Switch
                            checked={unlimited}
                            onCheckedChange={(checked) => onChange(checked ? String(unlimitedValue) : "")}
                            className="scale-90"
                        />
                    </label>
                )}
            </div>
            <div className="relative">
                <Input
                    id={id}
                    type={unlimited ? "text" : "number"}
                    inputMode="numeric"
                    min={min}
                    value={unlimited ? t("Unlimited") : value}
                    onChange={(event) => onChange(event.target.value)}
                    disabled={unlimited}
                    aria-invalid={!!error}
                    className={suffix && !unlimited ? "pe-12" : undefined}
                />
                {suffix && !unlimited && (
                    <span className="pointer-events-none absolute end-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">
                        {suffix}
                    </span>
                )}
            </div>
            {hint && <p className="text-xs text-muted-foreground">{hint}</p>}
            <FieldError message={error} />
        </div>
    );
}
