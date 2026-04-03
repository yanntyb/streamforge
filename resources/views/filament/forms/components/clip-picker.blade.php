@include('filament-forms::components.file-upload')

<div
    x-data="clipPicker({ wire: $wire })"
    x-on:livewire-upload-finish.window="extractThumbnail()"
>
    <template x-if="thumbnailPreview">
        <div class="mt-3">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Miniature extraite :</p>
            <img :src="thumbnailPreview" class="rounded-lg w-40 shadow-sm border border-gray-200 dark:border-gray-700" />
        </div>
    </template>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('clipPicker', ({ wire }) => ({
        thumbnailPreview: wire.thumbnail,

        extractThumbnail() {
            const input = this.$root.closest('.fi-fo-field-wrp')?.querySelector('input[type=file]');
            if (!input?.files?.[0]) return;

            const file = input.files[0];
            if (!file.type.startsWith('video/')) return;

            const video = document.createElement('video');
            video.preload = 'metadata';
            video.muted = true;
            video.playsInline = true;
            video.src = URL.createObjectURL(file);
            video.currentTime = 1;

            video.addEventListener('seeked', () => {
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                const base64 = canvas.toDataURL('image/jpeg', 0.8);
                this.thumbnailPreview = base64;
                wire.set('thumbnail', base64);
                URL.revokeObjectURL(video.src);
            }, { once: true });
        },
    }));
});
</script>
