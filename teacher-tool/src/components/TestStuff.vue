<template>
  <div class="p-4">
    <input type="file" @change="handleFile" accept=".csv" class="mb-4" />

    <div v-if="students.length">
      <h3>Students: {{ students }}</h3>
      <ul>
        <li v-for="(student, i) in students">
          {{ student }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const students = ref([])
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
        for (let i = 0; i < headers.length; i++) {
          if (!cols[i] || cols[i].trim() == '') {
            cols[i] = 'Unknown'
          }
        }
        return `${cols[0].trim()}, ${cols[1].trim()} (${cols[2].trim()})` 
      })
    
    
      

  }

  reader.readAsText(file)
}
</script>
