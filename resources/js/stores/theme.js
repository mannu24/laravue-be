import { defineStore } from 'pinia';

export const useThemeStore = defineStore('theme', {
    state: () => ({
        isDark: localStorage.getItem('theme') === 'dark' ||
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)
    }),

    actions: {
        switchTheme() {
            this.isDark = !this.isDark;
            localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
            document.documentElement.classList.toggle('dark');
        },
        toggleTheme() {
            if (document.startViewTransition) {
                document.startViewTransition(() => this.switchTheme());
            } else {
                this.switchTheme();
            }
        },
        initTheme() {
            document.documentElement.classList.add('dark');
        }
    }
});