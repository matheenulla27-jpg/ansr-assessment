import { router, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState, type FormEvent } from 'react';

interface SearchBarPageProps extends Record<string, unknown> {
    query?: string;
}

export default function SearchBar() {
    const activeQuery = usePage<SearchBarPageProps>().props.query ?? '';
    const [query, setQuery] = useState(activeQuery);
    const debounceTimer = useRef<ReturnType<typeof setTimeout> | null>(null);

    useEffect(() => {
        setQuery(activeQuery);
    }, [activeQuery]);

    useEffect(() => {
        const trimmedQuery = query.trim();

        if (
            trimmedQuery.length === 0 &&
            window.location.pathname.startsWith('/products/search')
        ) {
            const timer = setTimeout(() => {
                debounceTimer.current = null;
                router.get(
                    '/',
                    {},
                    {
                        preserveState: true,
                        preserveScroll: true,
                        replace: true,
                    },
                );
            }, 300);
            debounceTimer.current = timer;

            return () => {
                clearTimeout(timer);
                if (debounceTimer.current === timer) {
                    debounceTimer.current = null;
                }
            };
        }

        if (trimmedQuery.length < 2 || trimmedQuery === activeQuery) {
            return;
        }

        const timer = setTimeout(() => {
            debounceTimer.current = null;
            router.get(
                '/products/search',
                { q: trimmedQuery },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                },
            );
        }, 300);
        debounceTimer.current = timer;

        return () => {
            clearTimeout(timer);
            if (debounceTimer.current === timer) {
                debounceTimer.current = null;
            }
        };
    }, [activeQuery, query]);

    function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        if (debounceTimer.current !== null) {
            clearTimeout(debounceTimer.current);
            debounceTimer.current = null;
        }

        router.get(
            '/products/search',
            { q: query.trim() },
            { preserveScroll: true, preserveState: true },
        );
    }

    return (
        <form
            role="search"
            onSubmit={handleSubmit}
            className="flex max-w-xl gap-3"
        >
            <label htmlFor="catalogue-search" className="sr-only">
                Search products
            </label>
            <input
                id="catalogue-search"
                name="q"
                type="search"
                value={query}
                onChange={(event) => setQuery(event.target.value)}
                autoFocus
                placeholder="Search products"
                className="min-w-0 flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-950 placeholder:text-slate-500 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-300"
            />
            <button
                type="submit"
                className="rounded-lg bg-slate-900 px-5 py-2 font-medium text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2"
            >
                Search
            </button>
        </form>
    );
}
