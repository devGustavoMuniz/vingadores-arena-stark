import { Head, Link } from '@inertiajs/react';

interface Order {
    id: number;
    event_name: string;
    ticket_code: string;
    amount: number;
    confirmed_at: string;
}

interface Props {
    order: Order;
}

export default function OrderSuccess({ order }: Props) {
    const formattedPrice = new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(order.amount);

    const formattedDate = new Date(order.confirmed_at).toLocaleString('pt-BR');

    return (
        <>
            <Head title="Compra Confirmada! | Arena Stark" />

            <div className="min-h-screen bg-gray-950 text-white flex items-center justify-center px-4">
                <div className="w-full max-w-md text-center space-y-8">
                    {/* Success animation */}
                    <div className="text-8xl animate-bounce">🎉</div>

                    <div>
                        <h1 className="text-3xl font-extrabold text-white mb-2">
                            Compra confirmada!
                        </h1>
                        <p className="text-gray-400">
                            Seu ingresso para <strong className="text-white">{order.event_name}</strong> está garantido.
                        </p>
                    </div>

                    {/* Ticket card */}
                    <div className="rounded-2xl border border-emerald-500/30 bg-gray-900 p-8 space-y-6 text-left shadow-xl shadow-emerald-500/5">
                        <div className="flex items-center justify-between">
                            <span className="text-sm font-semibold text-gray-400 uppercase tracking-wider">Seu Ingresso</span>
                            <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-400">
                                <span className="h-1.5 w-1.5 rounded-full bg-emerald-400" />
                                Confirmado
                            </span>
                        </div>

                        {/* Ticket code */}
                        <div className="rounded-xl bg-gray-800 border border-dashed border-gray-700 p-6 text-center">
                            <p className="text-xs text-gray-500 mb-2">Código do Ingresso</p>
                            <p
                                id="ticket-code"
                                className="text-3xl font-mono font-bold tracking-widest text-amber-400"
                            >
                                {order.ticket_code}
                            </p>
                        </div>

                        <div className="space-y-3 text-sm">
                            <div className="flex justify-between">
                                <span className="text-gray-500">Evento</span>
                                <span className="text-white font-medium">{order.event_name}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-500">Valor pago</span>
                                <span className="text-amber-400 font-bold">{formattedPrice}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-500">Confirmado em</span>
                                <span className="text-white">{formattedDate}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-500">Pedido #</span>
                                <span className="text-white font-mono">{order.id}</span>
                            </div>
                        </div>
                    </div>

                    <Link
                        href="/events"
                        className="inline-block rounded-xl border border-gray-700 px-8 py-3 text-sm font-medium text-gray-400 hover:text-white hover:border-gray-600 transition-all"
                    >
                        Ver mais eventos
                    </Link>
                </div>
            </div>
        </>
    );
}
