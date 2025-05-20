import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import ThemeToggleButton from '@/components/ThemeToggleButton';
import { type BreadcrumbItem } from '@/types';
import { type ReactNode } from 'react';

interface AppLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
}

import { Head } from '@inertiajs/react';

export default ({ children, breadcrumbs, ...props }: AppLayoutProps) => (
    <>
      <Head>
        <script dangerouslySetInnerHTML={{__html:`(function(){try{var theme=localStorage.getItem('theme');if(theme==='dark'||(!theme&&window.matchMedia('(prefers-color-scheme: dark)').matches)){document.documentElement.classList.add('dark');}else{document.documentElement.classList.remove('dark');}}catch(e){}})();`}} />
      </Head>
      <ThemeToggleButton />
      <AppLayoutTemplate breadcrumbs={breadcrumbs} {...props}>
        {children}
      </AppLayoutTemplate>
    </>
);
