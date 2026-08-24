<template>
  <div class="trailer-player-box rounded-3 overflow-hidden border shadow-sm bg-black position-relative">
    <video ref="videoRef" class="w-100 h-100 object-fit-cover" controls playsinline preload="metadata"
      :poster="posterUrl" :src="videoUrl" @timeupdate="onTimeUpdate" @error="onError">
      Your browser does not support the video tag.
    </video>

    <div class="position-absolute top-0 start-50 translate-middle-x mt-3 pointer-events-none z-2 text-center">
      <span
        class="badge bg-black bg-opacity-75 text-warning font-monospace px-3 py-2 border border-warning-subtle shadow-lg">
        🎬 {{ movieTitle }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  spec: {
    type: Object,
    default: () => ({}),
  },
});

const videoRef = ref(null);
const defaultVideo = 'https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4';
const defaultPoster = 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=800&auto=format&fit=crop&q=80';

const videoUrl = computed(() => props.spec?.video_url || defaultVideo);
const posterUrl = computed(() => props.spec?.poster_url || defaultPoster);
const movieTitle = computed(() => props.spec?.title || 'MOVIE TRAILER');
const maxDuration = computed(() => props.spec?.duration || 7);

function onTimeUpdate(e) {
  const video = e.target;
  if (video && maxDuration.value > 0 && video.currentTime >= maxDuration.value) {
    video.pause();
    video.currentTime = 0;
  }
}

function onError(e) {
  const video = e.target;
  if (video && video.src !== defaultVideo) {
    video.src = defaultVideo;
    video.load();
  }
}
</script>

<style scoped>
.trailer-player-box {
  width: 100%;
  aspect-ratio: 16 / 9;
}

.pointer-events-none {
  pointer-events: none;
}

.object-fit-cover {
  object-fit: cover;
}
</style>
