import AppLayout from '../../layouts/AppLayout';
import type { UnavailablePageProps } from '../../types/catalogue';

export default function Unavailable({ message }: UnavailablePageProps) {
    return (
        <AppLayout>
            <section
                aria-labelledby="unavailable-heading"
                className="mx-auto max-w-2xl rounded-xl border border-amber-200 bg-white p-8 text-center shadow-sm sm:p-12"
                role="alert"
            >
                <p className="text-sm font-semibold uppercase tracking-wide text-amber-700">
                    Service unavailable
                </p>
                <h1
                    id="unavailable-heading"
                    className="mt-3 text-3xl font-semibold tracking-tight"
                >
                    We can’t load the catalogue right now
                </h1>
                <p className="mt-4 text-slate-600">{message}</p>
            </section>
        </AppLayout>
    );
}
