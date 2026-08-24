<template>
  <div>
    <div class="row g-4 mb-4">
      <div v-for="movie in movies" :key="movie.id" class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden movie-card hover-lift transition-all bg-white">
          <div class="position-relative bg-dark">
            <TrailerPlayer :spec="movie.trailer" />
            <div class="position-absolute top-0 start-0 m-2 z-2">
              <span class="badge bg-dark bg-opacity-75 text-white font-monospace">#{{ movie.index }}</span>
            </div>
            <div class="position-absolute top-0 end-0 m-2 z-2">
              <span class="badge bg-primary text-white fw-bold">{{ movie.rating }} ★</span>
            </div>
            <div class="position-absolute bottom-0 start-0 m-2 z-2">
              <span class="badge bg-dark bg-opacity-75 text-white rounded-pill px-2 py-1 font-monospace text-xs">
                {{ movie.likes_count }} 👍
              </span>
            </div>
          </div>

          <div class="card-body p-3 d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge bg-light text-dark border">{{ movie.genre }}</span>
                <small class="text-muted font-monospace">{{ movie.year }}</small>
              </div>

              <h6 class="fw-bold text-dark text-truncate mb-1" :title="movie.title">{{ movie.title }}</h6>
              
              <p class="text-muted text-xs mb-3 text-truncate" :title="movie.actors.join(', ')">
                <iconify-icon icon="solar:users-group-two-rounded-bold" class="me-1 text-primary"></iconify-icon>
                {{ movie.actors.join(', ') }}
              </p>
            </div>

            <div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center">
              <div class="d-flex gap-2">
                <span class="badge bg-danger-subtle text-danger rounded-pill px-2">
                  <iconify-icon icon="solar:heart-bold" class="me-1"></iconify-icon>
                  {{ movie.likes_count }}
                </span>
                <span class="badge bg-info-subtle text-info rounded-pill px-2">
                  <iconify-icon icon="solar:chat-round-line-bold" class="me-1"></iconify-icon>
                  {{ movie.reviews_count }}
                </span>
              </div>
              <button class="btn btn-sm btn-light text-primary rounded-circle p-1" @click="openDetails(movie)" title="View Details">
                <iconify-icon icon="solar:info-square-bold" width="20"></iconify-icon>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div ref="scrollTrigger" class="py-4 text-center">
      <div v-if="loading" class="spinner-border text-primary spinner-border-sm me-2" role="status">
        <span class="visually-hidden">Loading more...</span>
      </div>
      <span v-if="loading" class="text-muted text-sm font-medium">Loading next batch of movies...</span>
      <span v-else class="text-muted text-xs">Scroll down to load more movies automatically</span>
    </div>

    <div v-if="selectedMovie" class="modal fade show d-block bg-dark bg-opacity-50" tabindex="-1" @click.self="selectedMovie = null">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-3">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
              <span class="badge bg-primary">#{{ selectedMovie.index }}</span>
              {{ selectedMovie.title }} ({{ selectedMovie.year }})
            </h5>
            <button type="button" class="btn-close" @click="selectedMovie = null"></button>
          </div>
          <div class="modal-body p-4">
            <div class="row g-4">
              <div class="col-12 col-md-6">
                <TrailerPlayer :spec="selectedMovie.trailer" />
                <div class="mt-3 text-sm">
                  <p class="text-muted mb-2">{{ selectedMovie.synopsis }}</p>
                  <div class="text-muted"><strong>Director:</strong> {{ selectedMovie.director }}</div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <h6 class="fw-bold text-xs text-uppercase text-muted mb-2">Cast</h6>
                <div class="d-flex flex-wrap gap-1 mb-3">
                  <span v-for="actor in selectedMovie.actors" :key="actor" class="badge bg-light text-dark border">
                    {{ actor }}
                  </span>
                </div>

                <h6 class="fw-bold text-xs text-uppercase text-muted mb-2">Reviews ({{ selectedMovie.reviews_count }})</h6>
                <div class="reviews-scroll pe-1" style="max-height: 240px; overflow-y: auto;">
                  <div v-for="rev in selectedMovie.reviews" :key="rev.id" class="p-2 mb-2 bg-light rounded border-start border-3 border-primary text-xs">
                    <div class="d-flex justify-content-between font-medium mb-1">
                      <span>{{ rev.author }} ({{ rev.company }})</span>
                      <span class="text-warning">★ {{ rev.rating }}</span>
                    </div>
                    <p class="mb-0 text-muted">"{{ rev.comment }}"</p>
                  </div>
                  <div v-if="selectedMovie.reviews.length === 0" class="text-muted text-xs text-center py-3">
                    No reviews yet.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import TrailerPlayer from './TrailerPlayer.vue';

const props = defineProps({
  movies: Array,
  loading: Boolean,
});

const emit = defineEmits(['load-more']);

const scrollTrigger = ref(null);
const selectedMovie = ref(null);
let observer = null;

onMounted(() => {
  observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting && !props.loading) {
      emit('load-more');
    }
  }, { threshold: 0.1 });

  if (scrollTrigger.value) {
    observer.observe(scrollTrigger.value);
  }
});

onUnmounted(() => {
  if (observer) {
    observer.disconnect();
  }
});

function openDetails(movie) {
  selectedMovie.value = movie;
}
</script>

<style scoped>
.hover-lift:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08) !important;
}
.transition-all {
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
