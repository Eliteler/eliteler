import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from "@admin/components/ui/dialog";

/** Full-height preview of a theme / intro screenshot. */
export function PreviewDialog({
    open,
    onOpenChange,
    title,
    description,
    src,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title: string;
    description?: string;
    src: string | null;
}) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="flex max-h-[92svh] flex-col gap-3 overflow-hidden p-4 sm:max-w-sm">
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                    {description && (
                        <DialogDescription>{description}</DialogDescription>
                    )}
                </DialogHeader>

                {src && (
                    <div className="min-h-0 flex-1 overflow-y-auto">
                        <img
                            src={src}
                            alt={title}
                            className="block h-auto w-full rounded-xl border"
                        />
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
}
