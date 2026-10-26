import axios from "axios";
import { Check, Loader2 } from "lucide-react";
import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";

import { initials } from "@admin/components/nav-user";
import { Avatar, AvatarFallback, AvatarImage } from "@admin/components/ui/avatar";
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from "@admin/components/ui/command";

export type PickedCustomer = { id: string; name: string; email: string; avatar: string | null };

/**
 * Searchable list of active customers (admin.customers.search).
 */
export function CustomerPicker({
    value,
    onChange,
    exclude,
}: {
    value: PickedCustomer | null;
    onChange: (customer: PickedCustomer) => void;
    exclude?: string;
}) {
    const { t } = useTranslation();
    const [query, setQuery] = useState("");
    const [results, setResults] = useState<PickedCustomer[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const controller = new AbortController();
        setLoading(true);
        const timer = window.setTimeout(() => {
            axios
                .get<PickedCustomer[]>(route("admin.customers.search"), {
                    params: { q: query, except: exclude },
                    signal: controller.signal,
                })
                .then(({ data }) => setResults(data))
                .catch(() => undefined)
                .finally(() => setLoading(false));
        }, 250);

        return () => {
            window.clearTimeout(timer);
            controller.abort();
        };
    }, [query, exclude]);

    return (
        <Command shouldFilter={false} className="rounded-lg border">
            <CommandInput value={query} onValueChange={setQuery} placeholder={t("Search by name or email...")} />
            <CommandList className="max-h-64">
                {loading ? (
                    <div className="flex justify-center py-6">
                        <Loader2 className="size-4 animate-spin text-muted-foreground" />
                    </div>
                ) : (
                    <CommandEmpty>{t("No customers found.")}</CommandEmpty>
                )}
                {!loading && results.length > 0 && (
                    <CommandGroup>
                        {results.map((customer) => (
                            <CommandItem key={customer.id} value={customer.id} onSelect={() => onChange(customer)}>
                                <Avatar className="size-7">
                                    {customer.avatar && <AvatarImage src={customer.avatar} alt={customer.name} />}
                                    <AvatarFallback className="text-[10px]">{initials(customer.name)}</AvatarFallback>
                                </Avatar>
                                <span className="flex min-w-0 flex-1 flex-col">
                                    <span className="truncate text-sm">{customer.name}</span>
                                    <span className="truncate text-xs text-muted-foreground">{customer.email}</span>
                                </span>
                                {value?.id === customer.id && <Check className="text-primary" />}
                            </CommandItem>
                        ))}
                    </CommandGroup>
                )}
            </CommandList>
        </Command>
    );
}
