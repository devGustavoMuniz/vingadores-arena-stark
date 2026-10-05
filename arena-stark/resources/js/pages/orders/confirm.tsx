import { Head, Link, useForm } from '@inertiajs/react';

interface Props {
    token: string;
}

export default function OrderConfirm({ token }: Props) {
    const { post, processing } = useForm({});

    function handleConfirm(e: React.FormEvent) {
        e.preventDefault();
        post(`/orders/confirm/${token}`);
    }

    return (
        <>
            <Head title="Confirmar Compra | Arena Stark" />

            <div className="flex min-h-screen items-center justify-center bg-gray-950 px-4 text-white">
                <div className="w-full max-w-md">
                    <div className="space-y-6 rounded-2xl border border-amber-500/30 bg-gray-900 p-8 text-center shadow-xl shadow-amber-500/5">
                        {/* Timer icon */}
                        <div className="text-6xl">⏱️</div>

                        <div>
                            <h1 className="mb-2 text-2xl font-extrabold text-white">
                                Confirme sua compra
                            </h1>
                            <p className="text-gray-400">
                                Sua reserva está ativa por{' '}
                                <strong className="text-amber-400">
                                    10 minutos
                                </strong>
                                . Confirme agora para garantir seu ingresso.
                            </p>
                        </div>

                        <div className="space-y-2 rounded-xl border border-gray-700 bg-gray-800/60 p-4 text-left">
                            <div className="flex items-center gap-2 text-xs text-gray-500">
                                <span>🔑</span>
                                <span className="truncate font-mono text-gray-400">
                                    {token.slice(0, 16)}...
                                </span>
                            </div>
                            <p className="text-xs text-gray-500">
                                Token de reserva — válido até confirmação ou
                                expiração.
                            </p>
                        </div>

                        <form onSubmit={handleConfirm} className="space-y-3">
                            <button
                                id="btn-confirm-purchase"
                                type="submit"
                                disabled={processing}
                                className="w-full rounded-xl bg-amber-500 px-6 py-3.5 text-base font-bold text-gray-900 transition-all duration-200 hover:bg-amber-400 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                {processing
                                    ? 'Confirmando...'
                                    : '✅ Confirmar Compra'}
                            </button>

                            <Link
                                href="/events"
                                className="block w-full rounded-xl border border-gray-700 px-6 py-3.5 text-center text-sm font-medium text-gray-400 transition-all hover:border-gray-600 hover:text-white"
                            >
                                Cancelar e voltar aos eventos
                            </Link>
                        </form>
                    </div>
                </div>
            </div>
        </>
    );
}
