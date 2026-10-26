import * as React from "react";

/**
 * Laravel's logout redirects to the public website (not an Inertia page),
 * so it is submitted as a regular form instead of an Inertia visit.
 */
export const LogoutForm = React.forwardRef<HTMLFormElement>((_, ref) => {
    const token =
        document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.content ?? "";

    return (
        <form
            ref={ref}
            method="POST"
            action={route("logout")}
            className="hidden"
        >
            <input type="hidden" name="_token" value={token} />
        </form>
    );
});
LogoutForm.displayName = "LogoutForm";
