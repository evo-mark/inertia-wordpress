<template>
  <svg
    fill="none"
    :width="svgDimensions.width"
    :height="svgDimensions.height"
    xmlns="http://www.w3.org/2000/svg"
    :class="['animate-beam', $props.class]"
    :viewBox="`0 0 ${svgDimensions.width} ${svgDimensions.height}`"
  >
    <path
      :d="pathD"
      :stroke="pathColor"
      :stroke-width="pathWidth"
      :stroke-opacity="pathOpacity"
      stroke-linecap="round"
    />
    <path
      :d="pathD"
      :stroke-width="pathWidth"
      :stroke="`url(#${id})`"
      stroke-opacity="1"
      stroke-linecap="round"
    />
    <defs>
      <linearGradient
        :id="id"
        gradientUnits="userSpaceOnUse"
        x1="0%"
        x2="0%"
        y1="0%"
        y2="0%"
      >
        <stop :stop-color="gradientStartColor" stop-opacity="0" />
        <stop :stop-color="gradientStartColor" />
        <stop offset="32.5%" :stop-color="gradientStopColor" />
        <stop offset="100%" :stop-color="gradientStopColor" stop-opacity="0" />
        <animate
          attributeName="x1"
          :values="x1"
          :dur="`${duration}s`"
          keyTimes="0; 1"
          keySplines="0.16 1 0.3 1"
          calcMode="spline"
          repeatCount="indefinite"
        />
        <animate
          attributeName="x2"
          :values="x2"
          :dur="`${duration}s`"
          keyTimes="0; 1"
          keySplines="0.16 1 0.3 1"
          calcMode="spline"
          repeatCount="indefinite"
        />
      </linearGradient>
    </defs>
  </svg>
</template>

<script lang="ts" setup>
import { ref, watchEffect, onBeforeUnmount } from "vue";

type AnimatedBeamProps = {
  class?: string;
  containerRef: HTMLElement;
  fromRef: HTMLElement;
  toRef: HTMLElement;
  curvature?: number;
  reverse?: boolean;
  pathColor?: string;
  pathWidth?: number;
  pathOpacity?: number;
  gradientStartColor?: string;
  gradientStopColor?: string;
  delay?: number;
  duration?: number;
  startXOffset?: number;
  startYOffset?: number;
  endXOffset?: number;
  endYOffset?: number;
};

const props = withDefaults(defineProps<AnimatedBeamProps>(), {
  curvature: 0,
  reverse: false,
  duration: Math.random() * 3 + 4,
  delay: 0,
  pathColor: "gray",
  pathWidth: 2,
  pathOpacity: 0.2,
  gradientStartColor: "#FFAA40",
  gradientStopColor: "#9C40FF",
  startXOffset: 0,
  startYOffset: 0,
  endXOffset: 0,
  endYOffset: 0,
});

const id = "beam-" + Math.random().toString(36).substring(2, 10);
const x1 = props.reverse ? "90%; -10%;" : "10%; 110%;";
const x2 = props.reverse ? "100%; 0%;" : "0%; 100%;";

const pathD = ref("");
const svgDimensions = ref<{ width: number; height: number }>({
  width: 0,
  height: 0,
});

let resizeObserver: ResizeObserver | undefined = undefined;

const { stop: stopEffect } = watchEffect(effect);

function effect() {
  if (resizeObserver == undefined && props.containerRef != null) {
    resizeObserver = new ResizeObserver(() => {
      updatePath();
    });
    resizeObserver.observe(props.containerRef);

    stopEffect();
  }
}

// Function to update the path based on the positions of the elements
function updatePath() {
  if (props.containerRef && props.fromRef && props.toRef) {
    const containerRect = props.containerRef.getBoundingClientRect();
    const rectA = props.fromRef.getBoundingClientRect();
    const rectB = props.toRef.getBoundingClientRect();

    const svgWidth = containerRect.width;
    const svgHeight = containerRect.height;
    svgDimensions.value = { width: svgWidth, height: svgHeight };

    const startX =
      rectA.left -
      containerRect.left +
      rectA.width / 2 +
      (props.startXOffset ?? 0);
    const startY =
      rectA.top -
      containerRect.top +
      rectA.height / 2 +
      (props.startYOffset ?? 0);
    const endX =
      rectB.left -
      containerRect.left +
      rectB.width / 2 +
      (props.endXOffset ?? 0);
    const endY =
      rectB.top -
      containerRect.top +
      rectB.height / 2 +
      (props.endYOffset ?? 0);

    const controlY = startY - (props.curvature ?? 0);
    const d = `M ${startX},${startY} Q ${(startX + endX) / 2},${controlY} ${endX},${endY}`;
    pathD.value = d;
  }
}

onBeforeUnmount(() => {
  resizeObserver?.disconnect();
});
</script>

<style lang="postcss">
.animate-beam {
  pointer-events: none;
  position: absolute;
  left: 0;
  top: 0;
  transform: translate3d(0, 0, 0);
  stroke-width: 2;
}
</style>
