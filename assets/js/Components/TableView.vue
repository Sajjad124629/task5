<template>
  <div class="card border border-light-subtle shadow-sm rounded-3 overflow-hidden bg-white mb-4">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 custom-table">
        <thead class="bg-light border-bottom text-muted text-sm">
          <tr>
            <th scope="col" class="ps-4 py-3" style="width: 80px;">#</th>
            <th scope="col" class="py-3" style="width: 180px;">Genre</th>
            <th scope="col" class="py-3" style="width: 320px;">Title</th>
            <th scope="col" class="py-3" style="min-width: 250px;">Cast</th>
            <th scope="col" class="pe-4 py-3 text-end" style="width: 110px;">Year</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="movie in movies" :key="movie.id">
            <tr 
              class="cursor-pointer transition-colors"
              :class="expandedId === movie.id ? 'bg-primary-subtle border-primary-subtle fw-medium' : ''"
              @click="toggleExpand(movie.id)"
            >
              <td class="ps-4 font-monospace">
                <div class="d-flex align-items-center gap-2">
                  <iconify-icon 
                    :icon="expandedId === movie.id ? 'solar:alt-arrow-up-bold' : 'solar:alt-arrow-down-bold'" 
                    width="14"
                    class="text-muted"
                  ></iconify-icon>
                  <span class="fw-bold">{{ movie.index }}</span>
                </div>
              </td>
              <td>
                <span class="text-dark">{{ movie.genre }}</span>
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <span class="fw-semibold text-dark">{{ movie.title }}</span>
                  <span v-if="movie.is_series" class="badge bg-info-subtle text-info font-monospace text-xs">Series</span>
                </div>
              </td>
              <td class="text-muted text-sm text-truncate" style="max-width: 500px;" :title="movie.actors.join(', ')">
                {{ movie.actors.join(', ') }}
              </td>
              <td class="pe-4 text-end font-monospace text-dark">
                {{ movie.year }}
              </td>
            </tr>

            <tr v-if="expandedId === movie.id" :key="'expanded-' + movie.id" class="bg-white">
              <td colspan="5" class="p-4 border-bottom shadow-inner">
                <div class="row g-4">
                  <div class="col-12 col-md-5 col-lg-4">
                    <div class="position-relative rounded-3 overflow-hidden border shadow-sm bg-dark">
                      <TrailerPlayer :spec="movie.trailer" />
                      <div class="position-absolute bottom-0 start-0 m-3 z-3">
                        <span class="badge bg-primary text-white rounded-pill px-3 py-2 fs-7 fw-bold d-flex align-items-center gap-1 shadow-sm">
                          <span>{{ movie.likes_count }}</span>
                          <iconify-icon icon="solar:like-bold" width="14"></iconify-icon>
                        </span>
                      </div>
                    </div>
                  </div>

                  <div class="col-12 col-md-7 col-lg-8">
                    <div class="d-flex flex-wrap align-items-baseline gap-2 mb-2">
                      <h4 class="fw-bold text-dark mb-0">{{ movie.title }}</h4>
                      <span class="text-muted fs-5 fw-medium">{{ movie.year }}, {{ movie.genre }}</span>
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-3">
                      <span v-if="movie.is_top10" class="badge bg-secondary text-white fw-bold px-2 py-1">Top 10</span>
                      <span v-if="movie.is_series" class="badge bg-info text-white fw-bold px-2 py-1">
                        TV Series • {{ movie.seasons_count }} {{ movie.seasons_count > 1 ? 'Seasons' : 'Season' }}
                      </span>
                      <span class="badge bg-light text-dark border px-2 py-1 font-monospace">{{ movie.duration }}</span>
                      <span class="badge bg-light text-dark border px-2 py-1 font-monospace fw-bold">{{ movie.age_rating }}</span>
                    </div>

                    <div class="text-sm mb-3">
                      <div class="fst-italic text-muted">
                        <strong class="text-dark not-italic">Cast: </strong>
                        {{ movie.actors.join(', ') }}
                      </div>
                      <div class="fst-italic text-muted">
                        <strong class="text-dark not-italic">Director: </strong>
                        {{ movie.director }}
                      </div>
                    </div>

                    <p class="text-muted text-sm leading-relaxed mb-4">
                      {{ movie.synopsis }}
                    </p>
                    <div class="border-top pt-3">
                      <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold text-dark mb-0">
                          Reviews ({{ movie.reviews_count }})
                        </h6>
                        <span class="badge bg-light text-muted border font-monospace">Average: {{ movie.reviews_count }} per movie</span>
                      </div>

                      <div v-if="movie.reviews.length === 0" class="text-muted text-sm fst-italic p-2 bg-light rounded">
                        No reviews available for this movie with the current review setting.
                      </div>

                      <div v-else class="d-flex flex-column gap-3 pe-2" style="max-height: 240px; overflow-y: auto;">
                        <div v-for="rev in movie.reviews" :key="rev.id" class="text-sm p-2 bg-light rounded border-start border-3 border-primary">
                          <p class="mb-1 text-dark fw-medium">"{{ rev.comment }}"</p>
                          <div class="text-muted text-xs d-flex justify-content-between">
                            <span>— {{ rev.author }}, <span class="fst-italic">{{ rev.company }}</span></span>
                            <span class="text-warning fw-bold">★ {{ rev.rating }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div class="card-footer bg-white border-top py-3 d-flex justify-content-center align-items-center">
      <ul class="pagination pagination-sm mb-0 gap-1">
        <li class="page-item" :class="{ disabled: page <= 1 }">
          <button class="page-link rounded-2 border-0" @click="changePage(page - 1)">
            <iconify-icon icon="solar:alt-arrow-left-bold" width="14"></iconify-icon>
          </button>
        </li>

        <li v-for="p in paginationPages" :key="p" class="page-item">
          <button 
            class="page-link rounded-2 border-0 font-monospace fw-bold px-3" 
            :class="p === page ? 'btn-primary active text-white' : 'text-dark hover-bg-light'"
            @click="changePage(p)"
          >
            {{ p }}
          </button>
        </li>

        <li class="page-item">
          <button class="page-link rounded-2 border-0" @click="changePage(page + 1)">
            <iconify-icon icon="solar:alt-arrow-right-bold" width="14"></iconify-icon>
          </button>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import TrailerPlayer from './TrailerPlayer.vue';

const props = defineProps({
  movies: Array,
  page: Number,
});

const emit = defineEmits(['change-page']);

const expandedId = ref(null);

function toggleExpand(id) {
  expandedId.value = expandedId.value === id ? null : id;
}

function changePage(p) {
  if (p < 1) return;
  expandedId.value = null;
  emit('change-page', p);
}

const paginationPages = computed(() => {
  const current = props.page;
  const pages = [];
  const start = Math.max(1, current - 1);
  for (let i = start; i < start + 3; i++) {
    pages.push(i);
  }
  return pages;
});
</script>

<style scoped>
.custom-table tbody tr {
  cursor: pointer;
}
.transition-colors {
  transition: background-color 0.15s ease-in-out;
}
.fs-7 {
  font-size: 0.75rem;
}
</style>
