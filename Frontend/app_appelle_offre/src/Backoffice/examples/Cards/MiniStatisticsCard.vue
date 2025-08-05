<template>
  <div class="mb-4 card">
    <div class="p-3 card-body">
      <div class="d-flex" :class="directionReverse ? reverseDirection : ''">
        <div>
          <div
            class="text-center shadow icon icon-shape border-radius-md"
            :class="typeof icon === 'object' ? icon.background : ''"
          >
            <!-- ✅ Remplacement correct -->
            <font-awesome-icon
              v-if="typeof icon === 'object'"
              :icon="icon.component"
              class="text-lg opacity-10"
            />
            <i
              v-else
              class="text-lg opacity-10"
              :class="icon"
              aria-hidden="true"
            ></i>
          </div>
        </div>
        <div :class="classContent">
          <div class="numbers">
            <p
              class="mb-0 text-sm text-capitalize font-weight-bold"
              :class="title.color"
            >
              {{ typeof title === "string" ? title : title.text }}
            </p>
            <h5 class="mb-0 font-weight-bolder" :class="value.color">
              {{
                typeof value === "string" || typeof value === "number"
                  ? value
                  : value.text
              }}
              <span
                class="text-sm font-weight-bolder"
                :class="percentage.color"
              >
                {{
                  typeof percentage === "number" ||
                  typeof percentage === "string"
                    ? `${percentage}`
                    : ""
                }}

                {{
                  percentage && typeof percentage === "object"
                    ? `${percentage.value}`
                    : ""
                }}
              </span>
            </h5>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


<script>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

export default {
  name: "MiniStatisticsCard",
  components: {
    FontAwesomeIcon
  },
  props: {
    directionReverse: {
      type: Boolean,
      default: false,
    },
    title: {
      type: [Object, String],
      default: null,
    },
    value: {
      type: [Object, String, Number],
      required: true,
    },
    percentage: {
      type: [Object, String],
      default: () => ({
        color: "text-success",
      }),
    },
    icon: {
      type: [String, Object],
      default: () => ({
        background: "bg-white",
      }),
    },
    classContent: {
      type: String,
      default: "",
    },
  },
  data() {
    return {
      reverseDirection: "flex-row-reverse justify-content-between",
    };
  },
};
</script>
