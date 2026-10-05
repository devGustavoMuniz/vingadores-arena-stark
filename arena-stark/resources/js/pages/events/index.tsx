import { Head, Link } from '@inertiajs/react';

interface Event {
    id: number;
    name: string;
    description: string;
    venue: string;
    starts_at: string;
    price: number;
    available_tickets: number;
    is_available: boolean;
}

interface Props {
    events: Event[];
}

export default function EventsIndex({ events }: Props) {
    return (
        <>
            <Head title="Eventos | Arena Stark" />

            <div className="min-h-screen bg-gray-950 text-white">
                {/* Header */}
                <header className="sticky top-0 z-10 border-b border-gray-800 bg-gray-900/80 backdrop-blur-md">
                    <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
                        <Link
                            href="/"
                            className="text-xl font-bold tracking-tight text-amber-400"
                        >
                            ⚡ Arena Stark
                        </Link>
                        <nav className="flex gap-4 text-sm text-gray-400">
                            <Link
                                href="/login"
                                className="transition-colors hover:text-white"
                            >
                                Entrar
                            </Link>
                        </nav>
                    </div>
                </header>

                {/* Hero */}
                <section className="bg-gradient-to-b from-amber-950/30 to-gray-950 px-4 py-16 text-center">
                    <h1 className="mb-3 text-4xl font-extrabold tracking-tight">
                        Eventos Disponíveis
                    </h1>
                    <p className="mx-auto max-w-xl text-lg text-gray-400">
                        Compre seus ingressos com segurança. Reserva garantida
                        em tempo real com controle de estoque.
                    </p>
                </section>

                {/* Event Grid */}
                <main className="mx-auto max-w-6xl px-4 pb-16">
                    {events.length === 0 ? (
                        <div className="py-24 text-center text-gray-500">
                            <p className="mb-4 text-6xl">🎟️</p>
                            <p className="text-xl">
                                Nenhum evento disponível no momento.
                            </p>
                        </div>
                    ) : (
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {events.map((event) => (
                                <EventCard key={event.id} event={event} />
                            ))}
                        </div>
                    )}
                </main>
            </div>
        </>
    );
}

function EventCard({ event }: { event: Event }) {
    const formattedDate = new Date(event.starts_at).toLocaleDateString(
        'pt-BR',
        {
            day: '2-digit',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        },
    );

    const formattedPrice = new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(event.price);

    return (
        <Link
            href={`/events/${event.id}`}
            className="group block rounded-2xl border border-gray-800 bg-gray-900 p-6 transition-all duration-200 hover:border-amber-500/50 hover:bg-gray-800/80 hover:shadow-lg hover:shadow-amber-500/5"
        >
            <div className="mb-4 flex items-start justify-between">
                <span
                    className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ${
                        event.is_available
                            ? 'bg-emerald-500/15 text-emerald-400'
                            : 'bg-red-500/15 text-red-400'
                    }`}
                >
                    <span
                        className={`h-1.5 w-1.5 rounded-full ${event.is_available ? 'bg-emerald-400' : 'bg-red-400'}`}
                    />
                    {event.is_available
                        ? `${event.available_tickets} ingressos`
                        : 'Esgotado'}
                </span>
                <span className="text-lg font-bold text-amber-400">
                    {formattedPrice}
                </span>
            </div>

            <h2 className="mb-2 line-clamp-2 text-lg font-bold text-white transition-colors group-hover:text-amber-300">
                {event.name}
            </h2>

            <p className="mb-4 line-clamp-2 text-sm text-gray-400">
                {event.description}
            </p>

            <div className="space-y-1.5 text-sm text-gray-500">
                <div className="flex items-center gap-2">
                    <span>📍</span>
                    <span className="truncate">{event.venue}</span>
                </div>
                <div className="flex items-center gap-2">
                    <span>📅</span>
                    <span>{formattedDate}</span>
                </div>
            </div>
        </Link>
    );
}
