import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard, login, register } from '@/routes';

export default function Welcome() {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Rendee" />
            <div className="flex min-h-screen flex-col items-center justify-between bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
                <header className="flex w-full max-w-5xl justify-end">
                    <nav className="flex items-center gap-4 text-sm">
                        {auth.user ? (
                            <Link
                                href={dashboard()}
                                className="inline-block rounded-md border border-[#19140035] px-4 py-2 font-medium text-[#1b1b18] transition hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={login()}
                                    className="inline-block rounded-md px-4 py-2 font-medium text-[#1b1b18] transition hover:text-black dark:text-[#EDEDEC] dark:hover:text-white"
                                >
                                    Log in
                                </Link>
                                <Link
                                    href={register()}
                                    className="inline-block rounded-md bg-[#1b1b18] px-4 py-2 font-medium text-white transition hover:bg-black dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                                >
                                    Register
                                </Link>
                            </>
                        )}
                    </nav>
                </header>

                <main className="flex flex-col items-center justify-center text-center">
                    <h1 className="text-6xl font-bold tracking-tight sm:text-8xl">
                        Rendee
                    </h1>
                </main>

                <footer className="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                    &copy; {new Date().getFullYear()} Rendee
                </footer>
            </div>
        </>
    );
}
