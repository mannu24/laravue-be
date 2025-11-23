import { defineStore } from 'pinia';

export const useThemeStore = defineStore('theme', {
    state: () => {
        const isDark = localStorage.getItem('theme') === 'dark' ||
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
        
        // Initialize dark class on HTML element
        if (typeof document !== 'undefined') {
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
        
        return {
            isDark
        };
    },

    actions: {
        switchTheme() {
            this.isDark = !this.isDark;
            localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
            
            if (this.isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        },
        toggleTheme() {
            if (document.startViewTransition) {
                document.startViewTransition(() => this.switchTheme());
            } else {
                this.switchTheme();
            }
        },
        initTheme() {
            // Ensure dark class is set correctly based on current state
            if (this.isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    }
});