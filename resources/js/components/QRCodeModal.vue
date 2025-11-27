<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black/50" @click="$emit('close')"></div>

        <!-- Modal content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 z-50">
                <!-- Header -->
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        QR Code Login
                    </h3>
                    <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Case info -->
                <div class="mb-4 text-sm text-gray-600">
                    <p><strong>Case:</strong> {{ caseData.case_name }}</p>
                    <p><strong>Participant:</strong> {{ caseData.participant_email }}</p>
                    <p><strong>Generated:</strong> {{ formatDate(caseData.generated_at) }}</p>
                </div>

                <!-- Revoked status -->
                <div v-if="caseData.is_revoked" class="mb-4 p-3 bg-red-50 border border-red-200 rounded">
                    <p class="text-sm text-red-800 font-semibold">This QR code has been revoked</p>
                    <p class="text-xs text-red-600 mt-1">Reason: {{ caseData.revoked_reason }}</p>
                </div>

                <!-- QR Code display -->
                <div class="flex justify-center mb-4 p-4 bg-gray-50 rounded">
                    <div v-html="caseData.qr_code"></div>
                </div>

                <!-- URL display -->
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-700 mb-1">QR Code URL:</label>
                    <input
                        type="text"
                        :value="caseData.url"
                        readonly
                        class="w-full px-2 py-1 text-xs border border-gray-300 rounded bg-gray-50"
                        @click="$event.target.select()"
                    />
                </div>

                <!-- Action buttons -->
                <div class="flex gap-2">
                    <button
                        @click="downloadQRCode"
                        class="flex-1 px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded hover:bg-blue-100">
                        Download
                    </button>
                    <button
                        v-if="caseData.is_revoked"
                        @click="handleUnrevoke"
                        class="flex-1 px-3 py-2 text-sm font-medium text-green-600 bg-green-50 border border-green-200 rounded hover:bg-green-100">
                        Re-enable
                    </button>
                    <button
                        v-if="!caseData.is_revoked"
                        @click="handleRegenerate"
                        class="flex-1 px-3 py-2 text-sm font-medium text-orange-600 bg-orange-50 border border-orange-200 rounded hover:bg-orange-100">
                        Regenerate
                    </button>
                    <button
                        v-if="!caseData.is_revoked"
                        @click="handleRevoke"
                        class="flex-1 px-3 py-2 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded hover:bg-red-100">
                        Revoke
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'QRCodeModal',
    props: {
        show: {
            type: Boolean,
            default: false
        },
        caseData: {
            type: Object,
            required: true
        }
    },
    methods: {
        formatDate(dateString) {
            if (!dateString) return '';
            return new Date(dateString).toLocaleString();
        },

        downloadQRCode() {
            const svg = this.caseData.qr_code;
            const blob = new Blob([svg], { type: 'image/svg+xml' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `qr-code-${this.caseData.case_name}.svg`;
            link.click();
            URL.revokeObjectURL(url);
        },

        handleRegenerate() {
            this.$emit('regenerate', this.caseData.case_id);
        },

        handleRevoke() {
            const reason = prompt('Enter reason for revoking this QR code (optional):');
            if (reason !== null) { // null = cancelled
                this.$emit('revoke', this.caseData.case_id, reason);
            }
        },

        handleUnrevoke() {
            if (confirm('Re-enable this QR code? It will become valid again.')) {
                this.$emit('unrevoke', this.caseData.case_id);
            }
        }
    }
};
</script>
