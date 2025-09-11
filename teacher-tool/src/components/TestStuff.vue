<template>
  <div class="p-4">
    <input type="file" @change="handleFile" accept=".csv" class="mb-4" />

    <div v-if="students.length">
      <h3>Students: {{ students }}</h3>
      <ul>
        <li v-for="(student, i) in students" :key="i">
          {{ student }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const students = ref([])

function handleFile(e) {
  const file = e.target.files[0]
  if (!file) return

  const reader = new FileReader()
  reader.onload = (evt) => {
    const text = evt.target.result
    const rows = text.split(/\r?\n/)


    students.value = rows
      .map(line => line.split(','))            
      .filter(cols => cols[0] && cols[1])      
      .map(cols => `${cols[1].trim()} ${cols[0].trim()}`) 
  }

  reader.readAsText(file)
}
</script>
