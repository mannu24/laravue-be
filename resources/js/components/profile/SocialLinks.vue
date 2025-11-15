<!-- resources/js/components/SocialLinks.vue -->
<template>
    <div class="social-links bg-background text-foreground">
        <!-- Profile Section Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold tracking-tight">Social Profiles</h2>
                <p class="text-muted-foreground">Connect with me across platforms</p>
            </div>

            <!-- Only show Add button for profile owner -->
            <button @click="showAddForm = !showAddForm"
                class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                    <path d="M5 12h14"></path>
                    <path d="M12 5v14"></path>
                </svg>
                Add Link
            </button>
        </div>

        <!-- Add New Social Link Form (Collapsible) -->
        <div v-if="showAddForm"
            class="p-6 border rounded-lg shadow-sm bg-card text-card-foreground dark:border-border mb-6 transition-all">
            <h3 class="text-lg font-medium mb-4">Add New Social Profile</h3>
            <form @submit.prevent="addSocialLink">
                <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-end">
                    <div class="w-full sm:w-1/3">
                        <label class="block text-sm font-medium mb-1.5">Platform</label>
                        <div class="relative w-full">
                            <select v-model="newLink.social_link_type_id"
                                class="w-full h-10 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                required>
                                <option value="">Select Platform</option>
                                <option v-for="type in socialLinkTypes" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="w-full sm:w-1/2">
                        <label class="block text-sm font-medium mb-1.5">Username/URL</label>
                        <input v-model="newLink.username" type="text"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            placeholder="Enter username or full URL" required>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                            Save
                        </button>
                        <button type="button" @click="showAddForm = false"
                            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <!-- Social Links Display -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <template v-if="socialLinks.length">
                <TransitionGroup tag="div" name="list" class="grid grid-cols-1 md:grid-cols-2 gap-4 col-span-2">
                    <div v-for="element in socialLinks" :key="element.id" :class="[
                        'flex items-center gap-3 p-4 rounded-lg border transition-all',
                        isEditing ? 'cursor-grab bg-muted/40 dark:bg-muted/20 hover:bg-muted/60 dark:hover:bg-muted/30' : 'bg-card hover:border-primary/50',
                        !element.is_visible && isEditing ? 'opacity-60' : ''
                    ]">
                        <!-- Platform Icon -->
                        <div
                            class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-full bg-primary/10 text-primary">
                            <i :class="element.social_link_type.icon" class="text-xl"></i>
                        </div>

                        <!-- Link Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h4 class="font-medium">{{ element.social_link_type.name }}</h4>
                                <span v-if="!element.is_visible && isEditing"
                                    class="px-1.5 py-0.5 rounded-full text-xs font-medium bg-muted text-muted-foreground">
                                    Hidden
                                </span>
                            </div>

                            <a :href="element.url" target="_blank"
                                class="text-sm text-primary hover:underline truncate block"
                                @click="trackClick(element)">
                                {{ formatUsername(element.username) }}
                            </a>

                            <div v-if="isEditing" class="text-xs text-muted-foreground mt-1">
                                {{ element.clicks }} clicks · Added {{ formatDate(element.created_at) }}
                            </div>
                        </div>

                        <!-- Action Buttons (Only in Edit Mode) -->
                        <div v-if="isEditing" class="flex items-center gap-2">
                            <button @click="moveItem(element, -1)"
                                class="inline-flex items-center justify-center rounded-md text-xs font-medium h-8 w-8 border"
                                :disabled="socialLinks.indexOf(element) === 0">
                                ↑
                            </button>
                            <button @click="moveItem(element, 1)"
                                class="inline-flex items-center justify-center rounded-md text-xs font-medium h-8 w-8 border"
                                :disabled="socialLinks.indexOf(element) === socialLinks.length - 1">
                                ↓
                            </button>
                            <button @click="toggleVisibility(element)"
                                class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-xs font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 px-2"
                                :title="element.is_visible ? 'Hide from profile' : 'Show on profile'">
                                <svg v-if="element.is_visible" xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                                    <path
                                        d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68">
                                    </path>
                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61">
                                    </path>
                                    <line x1="2" x2="22" y1="2" y2="22"></line>
                                </svg>
                            </button>

                            <button @click="showDeleteConfirmation(element)"
                                class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-xs font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-destructive hover:text-destructive-foreground h-8 w-8"
                                title="Delete link">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M3 6h18"></path>
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </TransitionGroup>
            </template>

            <!-- Empty State -->
            <div v-if="!socialLinks.length"
                class="col-span-2 flex flex-col items-center justify-center py-12 text-center border rounded-lg">
                <div class="rounded-full bg-muted/50 p-4 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-muted-foreground">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <line x1="10" y1="14" x2="21" y2="3"></line>
                    </svg>
                </div>
                <h3 class="text-lg font-medium">No social profiles yet</h3>
                <p class="text-muted-foreground mt-1">Connect your social media accounts to share with others.</p>
            </div>
        </div>

        <!-- Edit Mode Toggle (For Profile Owner) -->
        <div class="mt-6 flex justify-end" v-if="socialLinks.length">
            <button @click="isEditing = !isEditing"
                class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                    <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                </svg>
                {{ isEditing ? 'Done Editing' : 'Edit Links' }}
            </button>
        </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <ConfirmDialog
      v-model:open="showDeleteDialog"
      title="Delete Social Link"
      description="Are you sure you want to delete this social link? This action cannot be undone."
      confirm-text="Delete"
      cancel-text="Cancel"
      variant="destructive"
      @confirm="deleteSocialLink"
    />
</template>

<script>
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue';

const authStore = useAuthStore()

export default {
    name: 'SocialLinks',
    components: {
        ConfirmDialog
    },
    data() {
        return {
            socialLinks: [],
            socialLinkTypes: [],
            newLink: {
                social_link_type_id: '',
                username: '',
            },
            isEditing: false,
            showAddForm: false,
            showDeleteDialog: false,
            linkToDelete: null,
        };
    },
    mounted() {
        this.fetchSocialLinks();
        this.fetchSocialLinkTypes();
    },
    methods: {
        async fetchSocialLinks() {
            try {
                console.log('Fetching social links...');
                const response = await axios.get('/api/v1/social-links', {
                    headers: {
                        Authorization: `Bearer ${authStore.token}`
                    }
                });
                console.log('Social links response:', response.data);
                this.socialLinks = response.data.data || [];
                console.log('Updated socialLinks:', this.socialLinks);
            } catch (error) {
                console.error('Error fetching social links:', error);
            }
        },
        async fetchSocialLinkTypes() {
            try {
                const response = await axios.get('/api/v1/social-links/types', {
                    headers: {
                        Authorization: `Bearer ${authStore.token}`
                    }
                });
                this.socialLinkTypes = response.data || [];
            } catch (error) {
                console.error('Error fetching social link types:', error);
            }
        },
        async addSocialLink() {
            try {
                const response = await axios.post('/api/v1/social-links', this.newLink, {
                    headers: {
                        Authorization: `Bearer ${authStore.token}`
                    }
                });
                this.socialLinks.push(response.data.data);
                this.newLink = { social_link_type_id: '', username: '' };
                this.showAddForm = false;
            } catch (error) {
                console.error('Error adding social link:', error);
            }
        },
        async toggleVisibility(link) {
            try {
                const response = await axios.put(`/api/v1/social-links/${link.id}`, {
                    ...link,
                    is_visible: !link.is_visible,
                }, {
                    headers: {
                        Authorization: `Bearer ${authStore.token}`
                    }
                });
                link.is_visible = response.data.data.is_visible;
            } catch (error) {
                console.error('Error toggling visibility:', error);
            }
        },
        showDeleteConfirmation(link) {
            this.linkToDelete = link;
            this.showDeleteDialog = true;
        },
        async deleteSocialLink() {
            if (!this.linkToDelete) return;
            
                try {
                await axios.delete(`/api/v1/social-links/${this.linkToDelete.id}`, {
                        headers: {
                            Authorization: `Bearer ${authStore.token}`
                        }
                    });
                this.socialLinks = this.socialLinks.filter(l => l.id !== this.linkToDelete.id);
                this.linkToDelete = null;
                } catch (error) {
                    console.error('Error deleting social link:', error);
            }
        },
        async moveItem(element, direction) {
            const currentIndex = this.socialLinks.indexOf(element);
            const newIndex = currentIndex + direction;

            if (newIndex >= 0 && newIndex < this.socialLinks.length) {
                // Create a new array with the moved item
                const newArray = [...this.socialLinks];
                newArray.splice(currentIndex, 1);
                newArray.splice(newIndex, 0, element);

                // Update the local array
                this.socialLinks = newArray;

                // Update the positions on the server
                await this.updateOrder();
            }
        },
        async updateOrder() {
            try {
                const updates = this.socialLinks.map((link, index) => ({
                    id: link.id,
                    position: index,
                }));

                await Promise.all(updates.map(link =>
                    axios.put(`/api/v1/social-links/${link.id}`, { position: link.position }, {
                        headers: {
                            Authorization: `Bearer ${authStore.token}`
                        }
                    })
                ));
            } catch (error) {
                console.error('Error updating order:', error);
            }
        },
        async trackClick(link) {
            // Optimistically update the UI
            link.clicks++;

            try {
                await axios.post(`/api/v1/social-links/${link.id}/click`, {}, {
                    headers: {
                        Authorization: `Bearer ${authStore.token}`
                    }
                });
            } catch (error) {
                // Revert the optimistic update if the API call fails
                link.clicks--;
                console.error('Error tracking click:', error);
            }
        },
        formatUsername(username) {
            // Remove http/https and trailing slashes for cleaner display
            return username.replace(/^https?:\/\//, '').replace(/\/$/, '');
        },
        formatDate(dateString) {
            const date = new Date(dateString);
            return new Intl.DateTimeFormat('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            }).format(date);
        }
    },
};
</script>

<style scoped>
.social-links {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.list-move,
/* apply transition to moving elements */
.list-enter-active,
.list-leave-active {
    transition: all 0.5s ease;
}

.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateX(30px);
}

/* ensure leaving items are taken out of layout flow so that moving
   animations can be calculated correctly. */
.list-leave-active {
    position: absolute;
}
</style>