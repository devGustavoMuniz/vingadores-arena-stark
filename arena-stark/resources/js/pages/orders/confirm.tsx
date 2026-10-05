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

            <div className="min-h-screen bg-gray-950 text-white flex items-center justify-center px-4">
                <div className="w-full max-w-md">
                    <div className="rounded-2xl border border-amber-500/30 bg-gray-900 p-8 text-center space-y-6 shadow-xl shadow-amber-500/5">
                        {/* Timer icon */}
                        <div className="text-6xl">⏱️</div>

                        <div>
                            <h1 className="text-2xl font-extrabold text-white mb-2">
                                Confirme sua compra
                            </h1>
                            <p className="text-gray-400">
                                Sua reserva está ativa por <strong className="text-amber-400">10 minutos</strong>.
                                Confirme agora para garantir seu ingresso.
                            </p>
                        </div>

                        <div className="rounded-xl bg-gray-800/60 border border-gray-700 p-4 text-left space-y-2">
                            <div className="flex items-center gap-2 text-xs text-gray-500">
                                <span>🔑</span>
                                <span className="font-mono text-gray-400 truncate">{token.slice(0, 16)}...</span>
                            </div>
                            <p className="text-xs text-gray-500">
                                Token de reserva — válido até confirmação ou expiração.
                            </p>
                        </div>

                        <form onSubmit={handleConfirm} className="space-y-3">
                            <button
                                id="btn-confirm-purchase"
                                type="submit"
                                disabled={processing}
                                className="w-full rounded-xl bg-amber-500 px-6 py-3.5 text-base font-bold text-gray-900 hover:bg-amber-400 disabled:opacity-60 disabled:cursor-not-allowed transition-all duration-200 active:scale-95"
                            >
                                {processing ? 'Confirmando...' : '✅ Confirmar Compra'}
                            </button>

                            <Link
                                href="/events"
                                className="block w-full rounded-xl border border-gray-700 px-6 py-3.5 text-sm font-medium text-gray-400 hover:text-white hover:border-gray-600 transition-all text-center"
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
