import AuthLayoutTemplate from '@/layouts/auth/auth-simple-layout';
import ThemeToggleButton from '@/components/ThemeToggleButton';

export default function AuthLayout({ children, title, description, ...props }: { children: React.ReactNode; title: string; description: string }) {
    return (
        <>
            <ThemeToggleButton />
            <AuthLayoutTemplate title={title} description={description} {...props}>
                {children}
            </AuthLayoutTemplate>
        </>
    );
}
