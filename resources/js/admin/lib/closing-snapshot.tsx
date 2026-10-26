import * as React from "react";

/*
 | Pages often close a dialog by clearing the state its content reads
 | (open={editing !== null}, {uploading && <Form />}), which empties the
 | dialog before its close animation runs. The dialog root shares its open
 | state here, and the content keeps showing what it had while it animates out.
 */
const OpenContext = React.createContext(true);

export function useControllableOpen(
    open: boolean | undefined,
    defaultOpen: boolean | undefined,
    onOpenChange: ((open: boolean) => void) | undefined,
): [boolean, (open: boolean) => void] {
    const [uncontrolled, setUncontrolled] = React.useState(defaultOpen ?? false);
    const isOpen = open ?? uncontrolled;
    const setOpen = (value: boolean) => {
        if (open === undefined) setUncontrolled(value);
        onOpenChange?.(value);
    };
    return [isOpen, setOpen];
}

export function OpenProvider({ open, children }: { open: boolean; children: React.ReactNode }) {
    return <OpenContext.Provider value={open}>{children}</OpenContext.Provider>;
}

export function useClosingSnapshot(children: React.ReactNode): React.ReactNode {
    const open = React.useContext(OpenContext);
    const last = React.useRef(children);
    if (open) last.current = children;
    return open ? children : last.current;
}
