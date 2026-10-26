export default function Dot({
    color,
    className,
}: {
    color: string;
    className?: string;
}) {
    return (
        <span
            className={`inline-block w-2 h-2 mt-0.5 rounded-full bg-${color} ${className}`}
        />
    );
}
