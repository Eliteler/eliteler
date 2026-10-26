import hotToast from "react-hot-toast";

/*
 | Thin wrapper so pages don't depend on react-hot-toast directly.
 | Styling lives on <Toaster> in components/flash-toaster.tsx.
 */
export const toast = {
    success: (message: string) => hotToast.success(message),
    error: (message: string) => hotToast.error(message),
    message: (message: string) => hotToast(message),
    dismiss: (id?: string) => hotToast.dismiss(id),
};
