import { Head } from '@inertiajs/react';
import BibleBooksGrid from '@/components/BibleBooksGrid';

export default function Welcome() {
    return (
        <>
            <Head title="Verso a verso - Bíblia explicada">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100 dark:from-slate-950 dark:to-slate-900 px-4 py-8 md:px-8 md:py-12">
                <div className="mx-auto max-w-7xl">
                    <header className="mb-12 text-center">
                        <h1 className="text-4xl font-bold tracking-tight text-slate-900 dark:text-slate-50 mb-3">Verso a verso</h1>
                        <p className="text-xl text-slate-600 dark:text-slate-400">Bíblia Sagrada</p>
                    </header>
                    <main className="rounded-xl bg-white shadow-sm dark:bg-slate-800 p-6 md:p-8 lg:p-10">
                        <BibleBooksGrid />
                    </main>
                </div>
            </div>
        </>
    );
}
