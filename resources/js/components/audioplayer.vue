<template>
  <div v-if="!deleted" class="audio-player-card">
    <!-- Delete Confirmation Modal -->
    <Modal
      v-if="showDeleteModal"
      title="Confirm Delete"
      :visible="showDeleteModal"
      @confirm="deleteFile"
      @cancel="showDeleteModal = false"
    >
      <p class="text-sm text-gray-600">
        Are you sure you want to delete this audio recording?
      </p>
      <p class="mt-2 text-sm font-semibold text-gray-900">{{ formattedName }}</p>
      <p class="mt-3 text-xs text-red-600">This action cannot be undone.</p>
    </Modal>

    <!-- Header with metadata -->
    <div class="audio-player-header">
      <div class="audio-metadata">
        <div class="audio-filename">{{ formattedName }}</div>
        <div class="audio-timestamp">{{ date }}</div>
      </div>
      <button
        @click="confirmDeleteFile"
        class="delete-btn"
        title="Delete recording"
      >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
          <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
        </svg>
      </button>
    </div>

    <!-- Waveform-style visualization -->
    <div class="waveform-container" @click="seek">
      <div class="waveform-track">
        <div class="waveform-progress" :style="{ width: percentComplete + '%' }">
          <div class="waveform-glow"></div>
        </div>
        <!-- Decorative waveform bars -->
        <div class="waveform-bars">
          <div v-for="i in 60" :key="i" class="waveform-bar" :style="{ height: getBarHeight(i) + '%' }"></div>
        </div>
      </div>
      <div class="time-display">
        <span class="time-current">{{ currentTime }}</span>
        <span class="time-separator">/</span>
        <span class="time-duration">{{ durationTime }}</span>
      </div>
    </div>

    <!-- Control panel -->
    <div class="audio-controls">
      <!-- Primary controls -->
      <div class="controls-primary">
        <button @click.prevent="stop" class="control-btn" title="Stop">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <path d="M16,4.995v9.808C16,15.464,15.464,16,14.804,16H4.997C4.446,16,4,15.554,4,15.003V5.196C4,4.536,4.536,4,5.196,4h9.808C15.554,4,16,4.446,16,4.995z"/>
          </svg>
        </button>

        <button @click.prevent="playing = !playing" class="control-btn control-btn-play" title="Play/Pause">
          <svg v-if="!playing" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <path d="M15,10.001c0,0.299-0.305,0.514-0.305,0.514l-8.561,5.303C5.51,16.227,5,15.924,5,15.149V4.852c0-0.777,0.51-1.078,1.135-0.67l8.561,5.305C14.695,9.487,15,9.702,15,10.001z"/>
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <path d="M15,3h-2c-0.553,0-1,0.048-1,0.6v12.8c0,0.552,0.447,0.6,1,0.6h2c0.553,0,1-0.048,1-0.6V3.6C16,3.048,15.553,3,15,3z M7,3H5C4.447,3,4,3.048,4,3.6v12.8C4,16.952,4.447,17,5,17h2c0.553,0,1-0.048,1-0.6V3.6C8,3.048,7.553,3,7,3z"/>
          </svg>
        </button>
      </div>

      <!-- Secondary controls -->
      <div class="controls-secondary">
        <button @click.prevent="innerLoop = !innerLoop" class="control-btn control-btn-small" :class="{ 'is-active': innerLoop }" title="Loop">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <path v-if="!innerLoop" d="M1,12V5h3v6h10V8l5,4.5L14,17v-3H3C1.895,14,1,13.104,1,12z"/>
            <path v-else d="M20,7v7c0,1.103-0.896,2-2,2H2c-1.104,0-2-0.897-2-2V7c0-1.104,0.896-2,2-2h7V3l4,3.5L9,10V8H3v5h14V8h-3V5h4C19.104,5,20,5.896,20,7z"/>
          </svg>
        </button>

        <button @click.prevent="download" class="control-btn control-btn-small" title="Download">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <path d="M15,7h-3V1H8v6H5l5,5L15,7z M19.338,13.532c-0.21-0.224-1.611-1.723-2.011-2.114C17.062,11.159,16.683,11,16.285,11h-1.757l3.064,2.994h-3.544c-0.102,0-0.194,0.052-0.24,0.133L12.992,16H7.008l-0.816-1.873c-0.046-0.081-0.139-0.133-0.24-0.133H2.408L5.471,11H3.715c-0.397,0-0.776,0.159-1.042,0.418c-0.4,0.392-1.801,1.891-2.011,2.114c-0.489,0.521-0.758,0.936-0.63,1.449l0.561,3.074c0.128,0.514,0.691,0.936,1.252,0.936h16.312c0.561,0,1.124-0.422,1.252-0.936l0.561-3.074C20.096,14.468,19.828,14.053,19.338,13.532z"/>
          </svg>
        </button>

        <div class="volume-control">
          <button @click.prevent="mute" class="control-btn control-btn-small" title="Volume">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
              <path v-if="!muted" d="M5.312,4.566C4.19,5.685-0.715,12.681,3.523,16.918c4.236,4.238,11.23-0.668,12.354-1.789c1.121-1.119-0.335-4.395-3.252-7.312C9.706,4.898,6.434,3.441,5.312,4.566z M14.576,14.156c-0.332,0.328-2.895-0.457-5.364-2.928C6.745,8.759,5.956,6.195,6.288,5.865c0.328-0.332,2.894,0.457,5.36,2.926C14.119,11.258,14.906,13.824,14.576,14.156zM15.434,5.982l1.904-1.906c0.391-0.391,0.391-1.023,0-1.414c-0.39-0.391-1.023-0.391-1.414,0L14.02,4.568c-0.391,0.391-0.391,1.024,0,1.414C14.41,6.372,15.043,6.372,15.434,5.982z M11.124,3.8c0.483,0.268,1.091,0.095,1.36-0.388l1.087-1.926c0.268-0.483,0.095-1.091-0.388-1.36c-0.482-0.269-1.091-0.095-1.36,0.388L10.736,2.44C10.468,2.924,10.642,3.533,11.124,3.8z M19.872,6.816c-0.267-0.483-0.877-0.657-1.36-0.388l-1.94,1.061c-0.483,0.268-0.657,0.878-0.388,1.36c0.268,0.483,0.877,0.657,1.36,0.388l1.94-1.061C19.967,7.907,20.141,7.299,19.872,6.816z"/>
              <path v-else d="M14.201,9.194c1.389,1.883,1.818,3.517,1.559,3.777c-0.26,0.258-1.893-0.17-3.778-1.559l-5.526,5.527c4.186,1.838,9.627-2.018,10.605-2.996c0.925-0.922,0.097-3.309-1.856-5.754L14.201,9.194z M8.667,7.941c-1.099-1.658-1.431-3.023-1.194-3.26c0.233-0.234,1.6,0.096,3.257,1.197l1.023-1.025C9.489,3.179,7.358,2.519,6.496,3.384C5.568,4.31,2.048,9.261,3.265,13.341L8.667,7.941z M18.521,1.478c-0.39-0.391-1.023-0.391-1.414,0L1.478,17.108c-0.391,0.391-0.391,1.024,0,1.414c0.391,0.391,1.023,0.391,1.414,0l15.629-15.63C18.912,2.501,18.912,1.868,18.521,1.478z"/>
            </svg>
          </button>
          <input
            v-model.lazy.number="volume"
            type="range"
            min="0"
            max="100"
            class="volume-slider"
          />
        </div>
      </div>
    </div>

    <audio
      :loop="innerLoop"
      ref="audiofile"
      :src="audioSrc"
      preload="auto"
      style="display: none"
    ></audio>
  </div>
</template>

<script>
import Modal from './global/modal.vue';
import { emitter } from '@/emitter';

const convertTimeHHMMSS = (val) => {
  const hhmmss = new Date(val * 1000).toISOString().substr(11, 8);
  return hhmmss.indexOf("00:") === 0 ? hhmmss.substr(3) : hhmmss;
};

export default {
  components: {
    Modal
  },
  props: {
    file: {
      type: Object,
      required: true
    },
    autoplay: {
      type: Boolean,
      default: false
    },
    loop: {
      type: Boolean,
      default: false
    },
    name: {
      type: String,
      required: true
    },
    date: {
      type: String,
      default: ''
    },
    caseid: {
      type: [String, Number],
      required: true
    }
  },
  name: "AudioPlayer",
  data() {
    return {
      audio: undefined,
      currentSeconds: 0,
      durationSeconds: 0,
      innerLoop: false,
      loaded: false,
      playing: false,
      previousVolume: 35,
      showVolume: true,
      deleted: false,
      volume: 100,
      formattedName: this.getFilenameWithoutExtension(),
      lastDuration: -1,
      durationCheckCount: 0,
      isDurationStable: false,
      showDeleteModal: false
    };
  },
  created() {
    this.innerLoop = this.loop;
  },
  async mounted() {
    this.audio = this.$refs.audiofile;

    // Fetch audio file from web endpoint if not already provided
    if (!this.file.audiofile && this.file.id) {
      try {
        const response = await window.axios.get(`/files/${this.file.id}`);
        this.file.audiofile = response.data.data;
      } catch (error) {
        console.error('Failed to fetch audio file:', error);
      }
    }

    this.audio.addEventListener("timeupdate", this.update);
    this.audio.addEventListener("durationchange", this.load);
    this.audio.addEventListener("pause", () => {
      this.playing = false;
    });
    this.audio.addEventListener("play", () => {
      this.playing = true;
    });
    this.audio.addEventListener("error", (e) => {
      console.error('Audio playback error:', this.audio.error);
    });
  },
  computed: {
    currentTime() {
      return convertTimeHHMMSS(this.currentSeconds);
    },
    durationTime() {
      return convertTimeHHMMSS(this.durationSeconds);
    },
    percentComplete() {
      return parseInt((this.currentSeconds / this.durationSeconds) * 100, 10);
    },
    muted() {
      return this.volume / 100 === 0;
    },
    audioSrc() {
      return this.file.audiofile || '';
    }
  },
  methods: {
    getFilenameWithoutExtension() {
      // Extract filename after last slash
      const filename = this.name.substring(this.name.lastIndexOf("/") + 1);
      // Remove file extension (everything after the last dot)
      const lastDotIndex = filename.lastIndexOf(".");
      return lastDotIndex > 0 ? filename.substring(0, lastDotIndex) : filename;
    },
    download() {
      this.stop();
      const a = document.createElement("a");
      a.href = this.file.audiofile;
      // Keep the original extension for download
      a.download = this.name.substring(this.name.lastIndexOf("/") + 1);
      a.click();
    },
    load() {
      if (isFinite(this.audio.duration)) {
        this.loaded = true;
        this.durationSeconds = parseInt(this.audio.duration, 10);
        return this.playing === this.autoPlay;
      }
    },
    update() {
      this.currentSeconds = parseInt(this.audio.currentTime, 10);
    },
    confirmDeleteFile() {
      this.showDeleteModal = true;
    },
    showSnackbar(message) {
      emitter.emit('show-snackbar', message);
    },
    deleteFile() {
      const self = this;
      // Get productionUrl from the root app or as an injected prop
      const productionUrl = this.$root?.productionUrl || '';

      // Close modal immediately
      this.showDeleteModal = false;

      window.axios
          .delete(
              `${window.location.origin + productionUrl}/cases/${
                  self.caseid
              }/files/${self.file.id}`,
              {file: self.file.id}
          )
          .then((response) => {
            self.stop();
            self.showSnackbar(response.data.message);
            self.deleted = true;
          })
          .catch((error) => {
            self.showSnackbar(
                "There was an error during the request - refresh page and try again"
            );
          });
    },
    mute() {
      if (this.muted) {
        return this.volume = this.previousVolume;
      }

      this.previousVolume = this.volume;
      this.volume = 0;
    },
    seek(e) {
      if (!this.playing || e.target.tagName === "SPAN") {
        return;
      }

      const el = e.target.getBoundingClientRect();
      const seekPos = (e.clientX - el.left) / el.width;
      const newTime = this.audio.duration * seekPos;

      // Check if the new time is within the seekable range
      for (let i = 0; i < this.audio.seekable.length; i++) {
        if (newTime >= this.audio.seekable.start(i) && newTime <= this.audio.seekable.end(i)) {
          this.audio.pause(); // pause while seeking
          this.audio.currentTime = newTime;
          this.audio.play();  // resume playback
          return;
        }
      }
    },
    stop() {
      this.playing = false;
      this.audio.currentTime = 0;
    },
    getBarHeight(index) {
      // Generate pseudo-random but deterministic waveform heights based on file ID
      const seed = this.file.id || 1;
      const random = (Math.sin(seed * index * 0.1) + 1) * 50;
      return 20 + random; // Height between 20% and 70%
    }
  },
  watch: {
    playing(value) {
      if (value) {
        return this.audio.play();
      }
      this.audio.pause();
    },
    volume(value) {
      this.showVolume = false;
      this.audio.volume = this.volume / 100;
    },
    // Reset player state when file changes (switching between entries)
    'file.id'(newId, oldId) {
      if (newId !== oldId) {
        this.stop();
        this.playing = false;
        this.currentSeconds = 0;
        this.durationSeconds = 0;
      }
    }
  },
  beforeUnmount() {
    // Clean up event listeners
    if (this.audio) {
      this.audio.removeEventListener("timeupdate", this.update);
      this.audio.removeEventListener("durationchange", this.load);
      this.audio.removeEventListener("pause", () => {
        this.playing = false;
      });
      this.audio.removeEventListener("play", () => {
        this.playing = true;
      });
    }
  }
};
</script>

<style scoped>
/* Main container */
.audio-player-card {
  background: linear-gradient(135deg, #f8f6f3 0%, #f0ede8 100%);
  border: 1px solid rgba(4, 108, 190, 0.1);
  border-radius: 12px;
  padding: 1.25rem;
  margin: 1rem 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.06);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  max-width: 550px;
}

.audio-player-card:hover {
  box-shadow: 0 4px 12px rgba(4, 108, 190, 0.08), 0 2px 4px rgba(0, 0, 0, 0.08);
  border-color: rgba(4, 108, 190, 0.2);
}

/* Header */
.audio-player-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid rgba(77, 192, 181, 0.15);
}

.audio-metadata {
  flex: 1;
  min-width: 0;
}

.audio-filename {
  font-size: 0.875rem;
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.25rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  letter-spacing: -0.01em;
}

.audio-timestamp {
  font-size: 0.75rem;
  color: #64748b;
  font-variant-numeric: tabular-nums;
}

.delete-btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background: rgba(217, 36, 66, 0.08);
  border-radius: 6px;
  cursor: pointer;
  color: #d92442;
  transition: all 0.2s ease;
  flex-shrink: 0;
  margin-left: 0.75rem;
}

.delete-btn svg {
  width: 16px;
  height: 16px;
}

.delete-btn:hover {
  background: rgba(217, 36, 66, 0.15);
  transform: scale(1.05);
}

.delete-btn:active {
  transform: scale(0.95);
}

/* Waveform visualization */
.waveform-container {
  margin-bottom: 1rem;
  cursor: pointer;
}

.waveform-track {
  position: relative;
  height: 64px;
  background: linear-gradient(to bottom, rgba(77, 192, 181, 0.05), rgba(4, 108, 190, 0.08));
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.waveform-progress {
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  background: linear-gradient(90deg, rgba(4, 108, 190, 0.2) 0%, rgba(77, 192, 181, 0.3) 100%);
  transition: width 0.1s linear;
  z-index: 2;
}

.waveform-glow {
  position: absolute;
  right: 0;
  top: 0;
  width: 3px;
  height: 100%;
  background: linear-gradient(180deg, transparent, #4dc0b5, transparent);
  box-shadow: 0 0 8px rgba(77, 192, 181, 0.6);
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 0.8; }
  50% { opacity: 1; }
}

.waveform-bars {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: space-around;
  padding: 0 4px;
  z-index: 1;
}

.waveform-bar {
  flex: 1;
  max-width: 3px;
  background: linear-gradient(to top, rgba(4, 108, 190, 0.15), rgba(77, 192, 181, 0.25));
  margin: 0 1px;
  border-radius: 2px;
  transition: all 0.3s ease;
}

.waveform-container:hover .waveform-bar {
  background: linear-gradient(to top, rgba(4, 108, 190, 0.25), rgba(77, 192, 181, 0.35));
}

.time-display {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.813rem;
  font-variant-numeric: tabular-nums;
  color: #475569;
  font-weight: 500;
}

.time-separator {
  color: #94a3b8;
}

.time-current {
  color: #046cbe;
}

/* Controls */
.audio-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.controls-primary,
.controls-secondary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.control-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background: white;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  color: #046cbe;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  padding: 0;
}

.control-btn svg {
  width: 20px;
  height: 20px;
  fill: currentColor;
}

.control-btn:not(.control-btn-small) {
  width: 42px;
  height: 42px;
}

.control-btn-small {
  width: 36px;
  height: 36px;
}

.control-btn-small svg {
  width: 18px;
  height: 18px;
}

.control-btn-play {
  background: linear-gradient(135deg, #046cbe 0%, #4dc0b5 100%);
  color: white;
  box-shadow: 0 2px 6px rgba(4, 108, 190, 0.3);
}

.control-btn:hover {
  transform: translateY(-1px) scale(1.05);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
}

.control-btn-play:hover {
  box-shadow: 0 4px 12px rgba(4, 108, 190, 0.4);
}

.control-btn:active {
  transform: translateY(0) scale(0.98);
}

.control-btn.is-active {
  background: rgba(77, 192, 181, 0.15);
  color: #4dc0b5;
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Volume control */
.volume-control {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.volume-slider {
  -webkit-appearance: none;
  appearance: none;
  width: 80px;
  height: 4px;
  background: linear-gradient(to right, rgba(4, 108, 190, 0.2), rgba(77, 192, 181, 0.3));
  border-radius: 2px;
  outline: none;
  cursor: pointer;
}

.volume-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 14px;
  height: 14px;
  background: linear-gradient(135deg, #046cbe, #4dc0b5);
  border-radius: 50%;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(4, 108, 190, 0.3);
  transition: all 0.2s ease;
}

.volume-slider::-webkit-slider-thumb:hover {
  transform: scale(1.2);
  box-shadow: 0 2px 6px rgba(4, 108, 190, 0.4);
}

.volume-slider::-moz-range-thumb {
  width: 14px;
  height: 14px;
  background: linear-gradient(135deg, #046cbe, #4dc0b5);
  border: none;
  border-radius: 50%;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(4, 108, 190, 0.3);
  transition: all 0.2s ease;
}

.volume-slider::-moz-range-thumb:hover {
  transform: scale(1.2);
  box-shadow: 0 2px 6px rgba(4, 108, 190, 0.4);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .audio-player-card {
    padding: 1rem;
  }

  .waveform-track {
    height: 52px;
  }

  .audio-controls {
    flex-wrap: wrap;
    gap: 0.75rem;
  }

  .controls-secondary {
    flex-wrap: wrap;
  }

  .volume-slider {
    width: 60px;
  }
}
</style>
