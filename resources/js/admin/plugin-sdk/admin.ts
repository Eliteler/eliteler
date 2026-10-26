/*
 | The plugin API: everything a plugin page may import from "@gobiz/admin".
 |
 | Core puts this module on window.GoBizAdmin.admin (plugin-runtime.ts).
 | Plugin builds don't bundle it: vite.plugin.config.ts turns
 | `import { Button } from "@gobiz/admin"` into a lookup on that object, and
 | tsconfig.plugin.json points "@gobiz/admin" here for types.
 |
 | Removing or renaming anything here is a breaking change: bump PLUGIN_API in
 | plugin-runtime.ts and AdminSharedProps::PLUGIN_API.
 */
export * from "@admin/components/ui/alert";
export * from "@admin/components/ui/avatar";
export * from "@admin/components/ui/badge";
export * from "@admin/components/ui/button";
export * from "@admin/components/ui/card";
export * from "@admin/components/ui/collapsible";
export * from "@admin/components/ui/dialog";
export * from "@admin/components/ui/dropdown-menu";
export * from "@admin/components/ui/input";
export * from "@admin/components/ui/label";
export * from "@admin/components/ui/popover";
export * from "@admin/components/ui/progress";
export * from "@admin/components/ui/select";
export * from "@admin/components/ui/separator";
export * from "@admin/components/ui/skeleton";
export * from "@admin/components/ui/switch";
export * from "@admin/components/ui/table";
export * from "@admin/components/ui/tabs";
export * from "@admin/components/ui/textarea";
export * from "@admin/components/ui/tooltip";

export { Combobox } from "@admin/components/combobox";
export { ConfirmDialog } from "@admin/components/confirm-dialog";
export { CopyButton } from "@admin/components/copy-button";
export { EmptyState } from "@admin/components/empty-state";
export { FieldError } from "@admin/components/field-error";
export { ImageInput } from "@admin/components/image-input";
export { ListToolbar } from "@admin/components/list-toolbar";
export { PageHeader } from "@admin/components/page-header";
export { Pagination } from "@admin/components/pagination";
export { RichTextEditor } from "@admin/components/rich-text-editor";

export { formatDate, fromNow } from "@admin/lib/date";
export { formatCompact, formatCurrency, formatNumber } from "@admin/lib/format";
export { toast } from "@admin/lib/toast";
export { cn } from "@admin/lib/utils";
