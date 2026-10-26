import { Moon, Sun } from "lucide-react";
import { useTranslation } from "react-i18next";

import { Button } from "@admin/components/ui/button";
import { useAppearance } from "@admin/hooks/use-appearance";

export function ThemeToggle() {
    const { t } = useTranslation();
    const { appearance, setAppearance } = useAppearance();
    const next = appearance === "dark" ? "light" : "dark";

    return (
        <Button
            variant="ghost"
            size="icon"
            onClick={() => setAppearance(next)}
            aria-label={next === "dark" ? t("Dark mode") : t("Light mode")}
        >
            {appearance === "dark" ? <Sun /> : <Moon />}
        </Button>
    );
}
