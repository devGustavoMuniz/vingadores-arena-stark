import { Head, Link, useForm } from '@inertiajs/react';

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
    event: Event;
}

export default function EventShow({ event }: Props) {
    const { post, processing } = useForm({ event_id: event.id });

    const formattedDate = new Date(event.starts_at).toLocaleDateString('pt-BR', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });

    const formattedPrice = new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(event.price);

    function handleReserve(e: React.FormEvent) {
        e.preventDefault();
        post('/orders/reserve');
    }

    return (
        <>
            <Head title={`${event.name} | Arena Stark`} />

            <div className="min-h-screen bg-gray-950 text-white">
                {/* Header */}
                <header className="border-b border-gray-800 bg-gray-900/80 backdrop-blur-md sticky top-0 z-10">
                    <div className="mx-auto max-w-4xl px-4 py-4 flex items-center gap-4">
                        <Link href="/events" className="text-gray-400 hover:text-white transition-colors text-sm">
                            ← Voltar para eventos
                        </Link>
                        <span className="text-gray-700">/</span>
                        <span className="text-amber-400 font-semibold text-sm truncate">{event.name}</span>
                    </div>
                </header>

                <main className="mx-auto max-w-4xl px-4 py-12">
                    <div className="grid gap-8 lg:grid-cols-3">
                        {/* Event Info */}
                        <div className="lg:col-span-2 space-y-6">
                            <div>
                                <div className="flex items-center gap-3 mb-3">
                                    <span
                                        className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ${
                                            event.is_available
                                                ? 'bg-emerald-500/15 text-emerald-400'
                                                : 'bg-red-500/15 text-red-400'
                                        }`}
                                    >
                                        <span className={`h-1.5 w-1.5 rounded-full ${event.is_available ? 'bg-emerald-400' : 'bg-red-400'}`} />
                                        {event.is_available
                                            ? `${event.available_tickets} ingressos disponíveis`
                                            : 'Esgotado'}
                                    </span>
                                </div>
                                <h1 className="text-3xl font-extrabold tracking-tight text-white mb-4">
                                    {event.name}
                                </h1>
                                <p className="text-gray-300 leading-relaxed text-lg">
                                    {event.description}
                                </p>
                            </div>

                            <div className="rounded-2xl border border-gray-800 bg-gray-900 p-6 space-y-4">
                                <h2 className="text-sm font-semibold text-gray-400 uppercase tracking-wider">
                                    Detalhes do Evento
                                </h2>
                                <div className="space-y-3">
                                    <div className="flex items-start gap-3">
                                        <span className="text-xl">📍</span>
                                        <div>
                                            <p className="text-xs text-gray-500 mb-0.5">Local</p>
                                            <p className="text-white font-medium">{event.venue}</p>
                                        </div>
                                    </div>
                                    <div className="flex items-start gap-3">
                                        <span className="text-xl">📅</span>
                                        <div>
                                            <p className="text-xs text-gray-500 mb-0.5">Data e hora</p>
                                            <p className="text-white font-medium capitalize">{formattedDate}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Purchase Card */}
                        <div className="lg:col-span-1">
                            <div className="sticky top-24 rounded-2xl border border-gray-800 bg-gray-900 p-6 space-y-4">
                                <div className="text-center">
                                    <p className="text-sm text-gray-400 mb-1">Preço por ingresso</p>
                                    <p className="text-4xl font-extrabold text-amber-400">{formattedPrice}</p>
                                </div>

                                <div className="border-t border-gray-800 pt-4 space-y-3">
                                    {event.is_available ? (
                                        <form onSubmit={handleReserve}>
                                            <button
                                                id="btn-reserve-ticket"
                                                type="submit"
                                                disabled={processing}
                                                className="w-full rounded-xl bg-amber-500 px-6 py-3 text-base font-bold text-gray-900 hover:bg-amber-400 disabled:opacity-60 disabled:cursor-not-allowed transition-all duration-200 active:scale-95"
                                            >
                                                {processing ? 'Reservando...' : '🎟️ Reservar Ingresso'}
                                            </button>
                                        </form>
                                    ) : (
                                        <button
                                            disabled
                                            className="w-full rounded-xl bg-gray-800 px-6 py-3 text-base font-bold text-gray-500 cursor-not-allowed"
                                        >
                                            Esgotado
                                        </button>
                                    )}

                                    <p className="text-xs text-center text-gray-500">
                                        Você tem 10 minutos para concluir a compra após a reserva.
                                    </p>
                                </div>

                                <div className="border-t border-gray-800 pt-4">
                                    <div className="flex items-center gap-2 text-xs text-gray-500">
                                        <span>🔒</span>
                                        <span>Reserva atômica via Redis — sem overselling</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </>
    );
}
