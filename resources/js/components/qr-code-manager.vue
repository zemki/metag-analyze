<template>
    <div class="p-6">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">QR Login Codes</h2>
            <p class="mt-2 text-gray-600">Manage QR codes for all cases in this project. QR codes are automatically generated when cases are created.</p>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm font-medium text-blue-600">Total QR Codes</p>
                <p class="text-3xl font-bold text-blue-900">{{ qrTokens.length }}</p>
            </div>
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-sm font-medium text-green-600">Active</p>
                <p class="text-3xl font-bold text-green-900">{{ activeCount }}</p>
            </div>
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm font-medium text-red-600">Expired/Revoked</p>
                <p class="text-3xl font-bold text-red-900">{{ qrTokens.length - activeCount }}</p>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center items-center py-12">
            <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <!-- QR Codes List -->
        <div v-else class="space-y-4">
            <div v-for="token in qrTokens" :key="token.id"
                 class="p-4 bg-white border rounded-lg shadow-sm"
                 :class="{'border-gray-300': !token.is_active || token.is_expired, 'border-green-300': token.is_active && !token.is_expired}">

                <div class="flex items-start justify-between">
                    <!-- QR Info -->
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <span v-if="token.is_active && !token.is_expired"
                                  class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded">
                                Active
                            </span>
                            <span v-else-if="token.is_expired"
                                  class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded">
                                Expired
                            </span>
                            <span v-else
                                  class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded">
                                Revoked
                            </span>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-900">{{ token.case_name }}</h3>
                        <p class="text-sm text-gray-600">{{ token.user_email }}</p>

                        <div class="mt-2 space-y-1 text-sm text-gray-500">
                            <p><strong>Created:</strong> {{ formatDate(token.created_at) }}</p>
                            <p v-if="token.expires_at"><strong>Expires:</strong> {{ formatDate(token.expires_at) }}</p>
                            <p v-else><strong>Expires:</strong> Never</p>
                            <p v-if="token.last_used_at"><strong>Last Used:</strong> {{ formatDate(token.last_used_at) }} ({{ token.usage_count }}x)</p>
                            <p v-else><strong>Usage:</strong> Never used</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col space-y-2 ml-4">
                        <button @click="showQrCode(token)"
                                class="px-3 py-1 text-sm font-medium text-white bg-blue-500 rounded hover:bg-blue-600">
                            View QR
                        </button>
                        <button @click="copyLink(token)"
                                class="px-3 py-1 text-sm font-medium text-blue-500 bg-white border border-blue-500 rounded hover:bg-blue-50">
                            Copy Link
                        </button>
                        <button @click="downloadQr(token)"
                                class="px-3 py-1 text-sm font-medium text-green-500 bg-white border border-green-500 rounded hover:bg-green-50">
                            Download
                        </button>
                        <button v-if="token.is_active"
                                @click="revokeToken(token)"
                                class="px-3 py-1 text-sm font-medium text-red-500 bg-white border border-red-500 rounded hover:bg-red-50">
                            Revoke
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="qrTokens.length === 0" class="py-12 text-center text-gray-500">
                <p class="text-lg">No QR codes yet.</p>
                <p class="mt-2">QR codes are automatically generated when you create cases.</p>
            </div>
        </div>

        <!-- QR Code Modal -->
        <div v-if="selectedToken"
             @click="selectedToken = null"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div @click.stop class="relative p-6 bg-white rounded-lg shadow-xl max-w-md">
                <button @click="selectedToken = null"
                        class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <h3 class="mb-4 text-xl font-bold text-gray-900">{{ selectedToken.case_name }}</h3>
                <img :src="selectedToken.qr_image" alt="QR Code" class="w-full mb-4 rounded" />
                <p class="mb-4 text-sm text-gray-600 break-all">{{ selectedToken.qr_url }}</p>

                <div class="flex space-x-2">
                    <button @click="downloadQr(selectedToken)"
                            class="flex-1 px-4 py-2 text-sm font-medium text-white bg-green-500 rounded hover:bg-green-600">
                        Download
                    </button>
                    <button @click="copyLink(selectedToken)"
                            class="flex-1 px-4 py-2 text-sm font-medium text-blue-500 bg-white border border-blue-500 rounded hover:bg-blue-50">
                        Copy Link
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'QrCodeManager',
    props: {
        projectId: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            loading: true,
            qrTokens: [],
            activeCount: 0,
            selectedToken: null
        };
    },
    mounted() {
        this.loadQrTokens();
    },
    methods: {
        async loadQrTokens() {
            this.loading = true;
            try {
                const response = await fetch(`/projects/${this.projectId}/qr-tokens`);
                const data = await response.json();
                this.qrTokens = data.qr_tokens;
                this.activeCount = data.active_count;
            } catch (error) {
                console.error('Error loading QR tokens:', error);
                alert('Failed to load QR codes');
            } finally {
                this.loading = false;
            }
        },
        formatDate(dateString) {
            if (!dateString) return 'Never';
            const date = new Date(dateString);
            return date.toLocaleString();
        },
        showQrCode(token) {
            this.selectedToken = token;
        },
        copyLink(token) {
            navigator.clipboard.writeText(token.qr_url);
            alert('Link copied to clipboard!');
        },
        downloadQr(token) {
            const link = document.createElement('a');
            link.href = token.qr_image;
            link.download = `qr-code-${token.case_name.replace(/[^a-z0-9]/gi, '-')}.png`;
            link.click();
        },
        async revokeToken(token) {
            if (!confirm(`Are you sure you want to revoke the QR code for "${token.case_name}"? This cannot be undone.`)) {
                return;
            }

            try {
                const response = await fetch(`/qr-tokens/${token.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    alert('QR code revoked successfully');
                    this.loadQrTokens();
                } else {
                    alert('Failed to revoke QR code');
                }
            } catch (error) {
                console.error('Error revoking token:', error);
                alert('Error revoking QR code');
            }
        }
    }
};
</script>
