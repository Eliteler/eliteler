import { Check, ChevronsUpDown } from "lucide-react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { Button } from "@admin/components/ui/button";
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from "@admin/components/ui/command";
import { Popover, PopoverContent, PopoverTrigger } from "@admin/components/ui/popover";
import { cn } from "@admin/lib/utils";

export type ComboboxOption = { value: string; label: string; hint?: string };

/** Searchable single select for long lists (timezones, currencies). */
export function Combobox({
    id,
    value,
    options,
    onChange,
    placeholder,
    invalid,
}: {
    id?: string;
    value: string;
    options: ComboboxOption[];
    onChange: (value: string) => void;
    placeholder?: string;
    invalid?: boolean;
}) {
    const { t } = useTranslation();
    const [open, setOpen] = useState(false);
    const selected = options.find((option) => option.value === value);

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button
                    id={id}
                    type="button"
                    variant="outline"
                    role="combobox"
                    aria-expanded={open}
                    aria-invalid={invalid}
                    className="w-full justify-between font-normal"
                >
                    <span className={cn("truncate", !selected && "text-muted-foreground")}>{selected?.label ?? placeholder ?? t("Select…")}</span>
                    <ChevronsUpDown className="opacity-50" />
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-(--radix-popover-trigger-width) min-w-64 p-0" align="start">
                <Command>
                    <CommandInput placeholder={t("Search…")} />
                    <CommandList className="max-h-72">
                        <CommandEmpty>{t("No results.")}</CommandEmpty>
                        <CommandGroup>
                            {options.map((option) => (
                                <CommandItem
                                    key={option.value}
                                    value={`${option.label} ${option.value} ${option.hint ?? ""}`}
                                    onSelect={() => {
                                        onChange(option.value);
                                        setOpen(false);
                                    }}
                                >
                                    <Check className={cn(option.value === value ? "opacity-100" : "opacity-0")} />
                                    <span className="truncate">{option.label}</span>
                                    {option.hint && <span className="ms-auto text-xs text-muted-foreground">{option.hint}</span>}
                                </CommandItem>
                            ))}
                        </CommandGroup>
                    </CommandList>
                </Command>
            </PopoverContent>
        </Popover>
    );
}
