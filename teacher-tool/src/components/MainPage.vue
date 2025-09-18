<template>
  <div class="w-[40vw] h-[80vh] bg-gray-800 shadow-lg rounded-xl border-gray-400 border-2 p-4 text-[var(--primary-color)] flex items-center justify-center m-4">
    <div class="text-center flex flex-col items-center">
      <h1 class="text-3xl font-bold mb-4">Welcome to the Teacher Tool</h1>
      <p class="text-lg">Upload a CSV file of students to get started.</p>
      <p class="text-sm">To download as a CSV file, go to File, Download, and select Comma Separated Values (.csv)</p>
      <p class="text-sm mt-2">Make sure the CSV has the following headers: lastname, firstname, osis</p>
      <img
        src="/images/example.png"
        alt="CSV Example"
        class="mt-4 max-w-full rounded-lg shadow-md"
      />
    </div>
  </div>

  <div class="scrollbar-hide w-[50vw] h-[80vh] bg-gray-600 shadow-lg rounded-xl border-gray-400 border-2 p-4 text-[var(--secondary-color)] overflow-auto flex items-center flex-col">
    <label class="w-1/2 cursor-pointer flex items-center justify-center px-4 py-3 mb-4 bg-indigo-600 text-[var(--primary-color)] rounded-lg shadow hover:bg-indigo-700">
      <i class="pi pi-upload mx-2"></i> Upload File
      <input type="file" @change="handleFile" accept=".csv" class="hidden border-black rounded-xl border-2 p-2" />
    </label>

    <div v-if="students.length" class="mt-4 w-5/6">
      <div class="scrollbar-hide rounded-xl border border-gray-200 bg-white shadow-md p-4 max-h-64 overflow-y-auto ">
        <h1 class="text-lg font-semibold mb-2">Students</h1>
        <ul class="space-y-1">
          <li
            v-for="student in students"
            class="px-2 py-1"
          >
            {{ student }}
          </li>
        </ul>
      </div>
    </div>

    <div v-if="students.length" class="mt-10 px-2">
      <div class="text-[var(--primary-color)] flex flex-wrap items-center gap-4 justify-center">
        <label class="flex flex-col text-sm font-medium">
          Number of groups:
          <input
            type="number"
            v-model.number="numGroups"
            min="1"
            class="mt-1 w-28 rounded-lg border border-gray-300 p-2 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:outline-none"
          />
        </label>

        <label class="flex flex-col text-sm font-medium">
          Min per group:
          <input
            type="number"
            v-model.number="minimumGroup"
            min="1"
            class="mt-1 w-28 rounded-lg border border-gray-300 p-2 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:outline-none"
          />
        </label>

        <label class="flex flex-col text-sm font-medium">
          Max per group:
          <input
            type="number"
            v-model.number="maximumGroup"
            min="1"
            class="mt-1 w-28 rounded-lg border border-gray-300 p-2 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:outline-none"
          />
        </label>
      </div>


      <div class="text-[var(--primary-color)] flex justify-center mt-4">
        <button
          @click="makeGroups(useMinMax, useNumGroups)"
          class="rounded-lg bg-blue-600 px-4 py-2 hover:bg-blue-700 focus:outline-none focus:ring focus:ring-blue-200"
        >
          Randomize Groups
        </button>
      </div>
    </div>

    <div v-if="groups.length" class="w-5/6 mt-4 px-2">
      <div
        v-for="(group, i) in groups"
        class="my-4 rounded-xl border border-gray-200 bg-white shadow-md hover:shadow-lg transition-shadow duration-200 p-4"
      >
        <h4 class="text-lg font-semibold mb-2">
          Group {{ i + 1 }}
        </h4>
        <ul class="space-y-1">
          <li
            v-for="student in group"
            class="rounded-md px-2 py-1"
          >
            {{ student }}
          </li>
        </ul>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'

const students = ref([])
const numGroups = ref(2)
const minimumGroup = ref(0)
const maximumGroup = ref(0)
const useMinMax = ref(false)
const useNumGroups = ref(true)
const groups = ref([])
const headers = ['lastname', 'firstname', 'osis']

function handleFile(e) {
  const file = e.target.files[0]
  if (!file) return

  const reader = new FileReader()
  reader.onload = (evt) => {
    const text = evt.target.result
    const rows = text.split(/\r?\n/)

    if (!rows.length) {
      error.value = "CSV file is empty."
      students.value = []
      return
    }

    const header = rows[0].split(',').map(h => h.trim().toLowerCase())
    for (let i; i < headers.length; i++) {
      if (!header.includes(headers[i])) {
        error.value = `CSV is missing required column: ${headers[i]}`
        students.value = []
        return
      }
    }

    const dataRows = rows.slice(1) 
    
    students.value = dataRows
      .map(line => {
        const cols = line.split(',')
        return `${cols[0].trim()}, ${cols[1].trim()} ${cols[2].trim()}` 
      }) 

  }

  reader.readAsText(file)
}

function makeGroups(useMinMax, useNumGroups) {
  if (!students.value.length) return
  let groupAmount = 0

  if (useMinMax) {
    groupAmount = Math.floor(students.value.length / minimumGroup.value)
  }
  if (useNumGroups) {
    groupAmount = numGroups.value
  }

  groups.value = Array.from({ length: groupAmount }, () => [])
  const shuffle = [...students.value].sort(() => Math.random() - 0.5)
  let groupIndex = 0

  for (const student of shuffle) {
    groups.value[groupIndex].push(student)
    groupIndex = (groupIndex + 1) % groupAmount
  }
}


</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.scrollbar-hide {
  -ms-overflow-style: none; 
  scrollbar-width: none;     
}
</style>
