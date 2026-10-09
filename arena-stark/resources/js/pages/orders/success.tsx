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

            <div className="flex min-h-screen items-center justify-center bg-gray-950 px-4 text-white">
                <div className="w-full max-w-md space-y-8 text-center">
                    {/* Success animation */}
                    <div className="animate-bounce text-8xl">🎉</div>

                    <div>
                        <h1 className="mb-2 text-3xl font-extrabold text-white">
                            Compra confirmada!
                        </h1>
                        <p className="text-gray-400">
                            Seu ingresso para{' '}
                            <strong className="text-white">
                                {order.event_name}
                            </strong>{' '}
                            está garantido.
                        </p>
                    </div>

                    {/* Ticket card */}
                    <div className="space-y-6 rounded-2xl border border-emerald-500/30 bg-gray-900 p-8 text-left shadow-xl shadow-emerald-500/5">
                        <div className="flex items-center justify-between">
                            <span className="text-sm font-semibold tracking-wider text-gray-400 uppercase">
                                Seu Ingresso
                            </span>
                            <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-400">
                                <span className="h-1.5 w-1.5 rounded-full bg-emerald-400" />
                                Confirmado
                            </span>
                        </div>

                        {/* Ticket code */}
                        <div className="rounded-xl border border-dashed border-gray-700 bg-gray-800 p-6 text-center">
                            <p className="mb-2 text-xs text-gray-500">
                                Código do Ingresso
                            </p>
                            <p
                                id="ticket-code"
                                className="font-mono text-3xl font-bold tracking-widest text-amber-400"
                            >
                                {order.ticket_code}
                            </p>
                        </div>

                        <div className="space-y-3 text-sm">
                            <div className="flex justify-between">
                                <span className="text-gray-500">Evento</span>
                                <span className="font-medium text-white">
                                    {order.event_name}
                                </span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-500">
                                    Valor pago
                                </span>
                                <span className="font-bold text-amber-400">
                                    {formattedPrice}
                                </span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-500">
                                    Confirmado em
                                </span>
                                <span className="text-white">
                                    {formattedDate}
                                </span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-500">Pedido #</span>
                                <span className="font-mono text-white">
                                    {order.id}
                                </span>
                            </div>
                        </div>
                    </div>

                    <Link
                        href="/events"
                        className="inline-block rounded-xl border border-gray-700 px-8 py-3 text-sm font-medium text-gray-400 transition-all hover:border-gray-600 hover:text-white"
                    >
                        Ver mais eventos
                    </Link>
                </div>
            </div>
        </>
    );
}
