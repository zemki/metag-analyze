<template>
  <div
      :class="
      !deleted
        ? 'relative flex flex-wrap px-2 ml-1 overflow-hidden border-2 border-blue-500 border-solid'
        : 'hidden'
    "
  >
    <div class="w-full px-1 my-1 overflow-hidden">
      <p class="text-base font-bold">{{ formattedName }}</p>
    </div>

    <div class="w-full px-1 mt-1 mb-2 overflow-hidden">
      <p class="text-xs">{{ date }}</p>
    </div>
    <div
        class="absolute top-0 right-0 mt-1 mr-1"
        v-on:click="confirmDeleteFile"
    >
      <svg
          style="color: red"
          xmlns="http://www.w3.org/2000/svg"
          width="20"
          height="20"
          fill="currentColor"
          class="bi bi-trash"
          viewBox="0 0 20 20"
      >
        <path
            d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"
            fill="red"
        ></path>
        <path
            fill-rule="evenodd"
            d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"
            fill="red"
        ></path>
      </svg>
    </div>
    <div class="flex w-full px-1 my-1">
      <div class="">
        <a v-on:click.prevent="stop" title="Stop" href="#">
          <svg
              width="18px"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
          >
            <path
                fill="currentColor"
                d="M16,4.995v9.808C16,15.464,15.464,16,14.804,16H4.997C4.446,16,4,15.554,4,15.003V5.196C4,4.536,4.536,4,5.196,4h9.808C15.554,4,16,4.446,16,4.995z"
            />
          </svg>
        </a>
      </div>
      <div class="ml-2">
        <a v-on:click.prevent="playing = !playing" title="Play/Pause" href="#">
          <svg
              width="18px"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
          >
            <path
                v-if="!playing"
                fill="currentColor"
                d="M15,10.001c0,0.299-0.305,0.514-0.305,0.514l-8.561,5.303C5.51,16.227,5,15.924,5,15.149V4.852c0-0.777,0.51-1.078,1.135-0.67l8.561,5.305C14.695,9.487,15,9.702,15,10.001z"
            />
            <path
                v-else
                fill="currentColor"
                d="M15,3h-2c-0.553,0-1,0.048-1,0.6v12.8c0,0.552,0.447,0.6,1,0.6h2c0.553,0,1-0.048,1-0.6V3.6C16,3.048,15.553,3,15,3z M7,3H5C4.447,3,4,3.048,4,3.6v12.8C4,16.952,4.447,17,5,17h2c0.553,0,1-0.048,1-0.6V3.6C8,3.048,7.553,3,7,3z"
            />
          </svg>
        </a>
      </div>
      <div class="ml-2">
        <div
            v-on:click="seek"
            class="w-64 bg-black player-progress"
            title="Time played : Total time"
        >
          <div
              :style="{ width: this.percentComplete + '%' }"
              class="z-20 bg-blue-500 player-seeker"
          ></div>
        </div>
        <div class="player-time-current">
          {{ currentTime }} - {{ durationTime }}
        </div>
      </div>
    </div>
    <div class="flex w-full px-1 my-1">
      <div class="ml-2">
        <a v-on:click.prevent="download" href="#">
          <svg
              width="18px"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
          >
            <path
                fill="currentColor"
                d="M15,7h-3V1H8v6H5l5,5L15,7z M19.338,13.532c-0.21-0.224-1.611-1.723-2.011-2.114C17.062,11.159,16.683,11,16.285,11h-1.757l3.064,2.994h-3.544c-0.102,0-0.194,0.052-0.24,0.133L12.992,16H7.008l-0.816-1.873c-0.046-0.081-0.139-0.133-0.24-0.133H2.408L5.471,11H3.715c-0.397,0-0.776,0.159-1.042,0.418c-0.4,0.392-1.801,1.891-2.011,2.114c-0.489,0.521-0.758,0.936-0.63,1.449l0.561,3.074c0.128,0.514,0.691,0.936,1.252,0.936h16.312c0.561,0,1.124-0.422,1.252-0.936l0.561-3.074C20.096,14.468,19.828,14.053,19.338,13.532z"
            />
          </svg>
        </a>
      </div>
      <div class="ml-2">
        <a v-on:click.prevent="innerLoop = !innerLoop" href="#">
          <svg
              width="18px"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
          >
            <path
                v-if="!innerLoop"
                fill="currentColor"
                d="M1,12V5h3v6h10V8l5,4.5L14,17v-3H3C1.895,14,1,13.104,1,12z"
            />
            <path
                v-else
                fill="currentColor"
                d="M20,7v7c0,1.103-0.896,2-2,2H2c-1.104,0-2-0.897-2-2V7c0-1.104,0.896-2,2-2h7V3l4,3.5L9,10V8H3v5h14V8h-3V5h4C19.104,5,20,5.896,20,7z"
            />
          </svg>
        </a>
      </div>
      <div class="ml-2">
        <a v-on:click.prevent="mute" title="Mute" href="#">
          <svg
              width="18px"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
          >
            <path
                v-if="!muted"
                fill="currentColor"
                d="M5.312,4.566C4.19,5.685-0.715,12.681,3.523,16.918c4.236,4.238,11.23-0.668,12.354-1.789c1.121-1.119-0.335-4.395-3.252-7.312C9.706,4.898,6.434,3.441,5.312,4.566z M14.576,14.156c-0.332,0.328-2.895-0.457-5.364-2.928C6.745,8.759,5.956,6.195,6.288,5.865c0.328-0.332,2.894,0.457,5.36,2.926C14.119,11.258,14.906,13.824,14.576,14.156zM15.434,5.982l1.904-1.906c0.391-0.391,0.391-1.023,0-1.414c-0.39-0.391-1.023-0.391-1.414,0L14.02,4.568c-0.391,0.391-0.391,1.024,0,1.414C14.41,6.372,15.043,6.372,15.434,5.982z M11.124,3.8c0.483,0.268,1.091,0.095,1.36-0.388l1.087-1.926c0.268-0.483,0.095-1.091-0.388-1.36c-0.482-0.269-1.091-0.095-1.36,0.388L10.736,2.44C10.468,2.924,10.642,3.533,11.124,3.8z M19.872,6.816c-0.267-0.483-0.877-0.657-1.36-0.388l-1.94,1.061c-0.483,0.268-0.657,0.878-0.388,1.36c0.268,0.483,0.877,0.657,1.36,0.388l1.94-1.061C19.967,7.907,20.141,7.299,19.872,6.816z"
            />
            <path
                v-else
                fill="currentColor"
                d="M14.201,9.194c1.389,1.883,1.818,3.517,1.559,3.777c-0.26,0.258-1.893-0.17-3.778-1.559l-5.526,5.527c4.186,1.838,9.627-2.018,10.605-2.996c0.925-0.922,0.097-3.309-1.856-5.754L14.201,9.194z M8.667,7.941c-1.099-1.658-1.431-3.023-1.194-3.26c0.233-0.234,1.6,0.096,3.257,1.197l1.023-1.025C9.489,3.179,7.358,2.519,6.496,3.384C5.568,4.31,2.048,9.261,3.265,13.341L8.667,7.941z M18.521,1.478c-0.39-0.391-1.023-0.391-1.414,0L1.478,17.108c-0.391,0.391-0.391,1.024,0,1.414c0.391,0.391,1.023,0.391,1.414,0l15.629-15.63C18.912,2.501,18.912,1.868,18.521,1.478z"
            />
          </svg>
        </a>
      </div>
      <div class="ml-2">
        <svg
            title="Volume"
            width="18px"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
        >
          <path
              fill="currentColor"
              d="M19,13.805C19,14.462,18.462,15,17.805,15H1.533c-0.88,0-0.982-0.371-0.229-0.822l16.323-9.055C18.382,4.67,19,5.019,19,5.9V13.805z"
          />
        </svg>
        <input v-model.lazy.number="volume" type="range" min="0" max="100"/>
      </div>
      <audio

          :loop="innerLoop"
          ref="audiofile"
          :src="file.audiofile"
          preload="metadata"
          style="display: none"
      ></audio>
    </div>
  </div>
</template>

<script>
const convertTimeHHMMSS = (val) => {
  const hhmmss = new Date(val * 1000).toISOString().substr(11, 8);

  return hhmmss.indexOf("00:") === 0 ? hhmmss.substr(3) : hhmmss;
};

export default {
  props: ["file", "autoplay", "loop", "name", "date", "caseid"],
  name: "audioPlayer",
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
      formattedName: `${this.name.substring(
          this.name.lastIndexOf("/") + 1,
          this.name.length
      )}.mp3`,
      lastDuration: -1,
      durationCheckCount: 0,
      isDurationStable: false
    };
  },
  created() {
    this.innerLoop = this.loop;
  },
  mounted() {
    // eslint-disable-next-line prefer-destructuring
    this.audio = this.$el.querySelectorAll("audio")[0];
    console.log(this.audio);
    this.audio.addEventListener("timeupdate", this.update);
    this.audio.addEventListener("durationchange", this.load);
    this.audio.addEventListener("pause", () => {
      this.playing = false;
    });
    this.audio.addEventListener("play", () => {
      this.playing = true;
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
  },
  methods: {
    download() {
      this.stop();
      const a = document.createElement("a");
      a.href = this.file.audiofile;
      a.download = `${this.name.substring(
          this.name.lastIndexOf("/") + 1,
          this.name.length
      )}.mp3`;
      a.click();
    },
    load() {

      if (isFinite(this.audio.duration)) {
        this.loaded = true;
        this.durationSeconds = parseInt(this.audio.duration, 10);
        console.log('Loaded', this.durationSeconds); // Add this
        console.log(`Duration changed: ${this.audio.duration}`);
        return this.playing === this.autoPlay;
      }
    },


    update() {
      this.currentSeconds = parseInt(this.audio.currentTime, 10);

    },

    confirmDeleteFile() {
      this.$buefy.dialog.confirm({
        title: "Confirm Delete",
        message:
            '<div class="p-2 text-center text-white bg-red-600">You re about to delete this File.<br><span class="has-text-weight-bold">Continue?</span></div>',
        cancelText: "Cancel",
        confirmText: "YES delete File",
        hasIcon: true,
        type: "is-danger",
        onConfirm: () => this.deleteFile(),
      });
    },
    deleteFile() {
      const self = this;
      window.axios
          .delete(
              `${window.location.origin + self.productionUrl}/cases/${
                  self.caseid
              }/files/${self.file.id}`,
              {file: self.file.id}
          )
          .then((response) => {
            self.stop();
            self.$buefy.snackbar.open(response.data.message);
            self.deleted = true;
          })
          .catch((error) => {
            self.$buefy.snackbar.open(
                "There it was an error during the request - refresh page and try again"
            );
          });
    },
    mute() {
      if (this.muted) {
        return this.volume === this.previousVolume;
      }

      this.previousVolume = this.volume;
      this.volume = 0;
    },
    seek(e) {
      console.log("Seek method called");
      if (!this.playing || e.target.tagName === "SPAN") {
        console.log("Seek early exit");
        return;
      }

      // Check if audio is seekable
      if (this.audio.seekable.length === 0) {
        console.log("Audio is not seekable yet");
        return;
      }

      const el = e.target.getBoundingClientRect();
      const seekPos = (e.clientX - el.left) / el.width;
      const newTime = this.audio.duration * seekPos;

      console.log(`Seek position: ${seekPos}`);
      console.log(`New time: ${newTime}`);

      // Check if the new time is within the seekable range
      if (newTime >= this.audio.seekable.start(0) && newTime <= this.audio.seekable.end(0)) {
        console.log("New time is in seekable range");
        this.audio.currentTime = newTime;
        return;
      }

      console.log("New time is NOT in seekable range");
    },


    stop() {
      this.playing = false;
      this.audio.currentTime = 0;
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
  },
};
</script>

<style scoped>
.player-progress {
  background-color: gray;
  cursor: pointer;
  height: 50%;
  max-width: 200px;
  position: relative;
}

.player-seeker {
  bottom: 0;
  left: 0;
  position: absolute;
  top: 0;
}
</style>
