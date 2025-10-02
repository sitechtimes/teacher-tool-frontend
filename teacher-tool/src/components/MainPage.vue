<template>
  <div class="w-[40vw] h-[80vh] bg-gray-800 shadow-lg rounded-xl border-gray-400 border-2 p-4 text-[var(--primary-color)] flex items-center justify-center m-4">
    <div class="text-center flex flex-col items-center">
      <h1 class="text-3xl font-bold mb-4">Welcome to the Teacher Tool</h1>
      <p class="text-lg">Upload a CSV (single sheet) or XLSX (multiple sheets) file of students to get started.</p>
      <p class="text-sm mt-2">Make sure the file has the headers: lastname, firstname, osis (case-insensitive)</p>
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
      <input type="file" @change="handleFile" accept=".csv, .xlsx, .xls" class="hidden border-black rounded-xl border-2 p-2" />
    </label>
    <button class="rounded-lg bg-blue-600 px-4 py-2 hover:bg-blue-700 focus:outline-none focus:ring focus:ring-blue-200"
     @click="SortedFileDownload()"> 
     Download
    </button>

    <div v-if="students.length" class="mt-4 w-5/6">
      <div
        v-for="(sheet, index) in Array.isArray(students[0]) || typeof students[0] === 'string'
                ? [{ name: 'Students', students: students }]
                : students"
        :key="index"
        class="scrollbar-hide rounded-xl border border-gray-200 bg-white shadow-md p-4 max-h-64 overflow-y-auto mb-4"
      >
        <h1 class="text-lg font-semibold mb-2">{{ sheet.name }}</h1>
        <ul class="space-y-1">
          <li
            v-for="student in sheet.students"
            :key="student"
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
          Grouping option:
          <select
            v-model="selectedOption"
            @change="ChangeBooleans"
            class="text-[var(--secondary-color)] bg-[var(--primary-color)] mt-1 w-40 rounded-lg border border-gray-300 p-2 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:outline-none"
          >
            <option value="numGroups">Number of Groups</option>
            <option value="min">Min per Group</option>
            <option value="max">Max per Group</option>
          </select>
        </label>

        <label class="flex flex-col text-sm font-medium">
          Number Value:
          <input
            type="number"
            v-model.number="inputValue"
            min="1"
            class="text-[var(--secondary-color)] bg-[var(--primary-color)] mt-1 w-28 rounded-lg border border-gray-300 p-2 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:outline-none"
          />
        </label>
      </div>

      <div class="text-[var(--primary-color)] flex justify-center mt-4">
        <button
          @click="makeGroups"
          class="rounded-lg bg-blue-600 px-4 py-2 hover:bg-blue-700 focus:outline-none focus:ring focus:ring-blue-200"
        >
          Randomize Groups
        </button>
      </div>
    </div>

    <div v-if="groups.length" class="w-5/6 mt-4 px-2">
      <div
        v-for="(sheet, si) in groups"
        :key="si"
        class="mb-8"
      >
        <h3 class="text-xl text-[var(--primary-color)] font-bold mb-2">{{ sheet.name }}</h3>

        <div
          v-for="(group, gi) in sheet.groups"
          class="my-4 rounded-xl border border-gray-200 bg-white shadow-md hover:shadow-lg transition-shadow duration-200 p-4"
        >
          <h4 class="text-lg font-semibold mb-2">
            Group {{ gi + 1 }}
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
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { toRaw } from 'vue';
import * as XLSX from 'xlsx'
const uploadedFile = ref(null)
const selectedOption = ref('numGroups')
const students = ref([])
const inputValue = ref(1)
const useMin = ref(false)
const useMax = ref(false)
const useNumGroups = ref(true)
const groups = ref([])
const error = ref('')
const headers = ['lastname', 'firstname', 'osis']

function SortedFileDownload(){ /* This is for XLSX files download | Only file format to support multple tabs */
  const workbook = XLSX.utils.book_new();
  const raw = toRaw(groups.value); 
  for(let i=0; i < raw.length; i++){
      let y = raw[i].groups
      const sheetData = [];
      sheetData.push(['LastName', 'FirstName', 'OSIS']);
      for(let p = 0; p < y.length; p++){
        let o = p + 1; /* Starts groups at 1 */
        const currentgroup = "Group " + o
        sheetData.push([currentgroup])
        const words = y[p]
        for(let k = 0; k < words.length; k++){
          let Splitingwords = words[k]
          let splitwords = Splitingwords.split(" ")
          sheetData.push(splitwords) 
        }
      }
      const worksheet = XLSX.utils.aoa_to_sheet(sheetData);
      XLSX.utils.book_append_sheet(workbook, worksheet, raw[i].name);
  }
  let file = uploadedFile.value 
  const isCSV = file.type === 'text/csv' || /\.csv$/i.test(file.name)
  const isXLSX = /\.(xlsx|xls)$/i.test(file.name)
  if (isCSV) {
  const worksheet = XLSX.utils.json_to_sheet(raw);
  XLSX.writeFile(workbook, 'grouped-students.csv');
  }
  if (isXLSX) {
  const worksheet = XLSX.utils.json_to_sheet(raw);
  XLSX.writeFile(workbook, 'grouped-students.xlsx');

  }

  
}













function handleFile(e) {
  const file = e.target.files[0]
  if (!file) return
  uploadedFile.value = file
  error.value = ''
  students.value = []

  const isCSV = file.type === 'text/csv' || /\.csv$/i.test(file.name)
  const isExcel = /\.(xlsx|xls)$/i.test(file.name)

  if (isCSV) {
    const reader = new FileReader()
    reader.onload = (evt) => {
      const text = evt.target.result
      const rows = text.split(/\r?\n/).filter(r => r.trim() !== '')
      if (rows.length === 0) {
        error.value = "CSV file is empty."
        return
      }

      const header = rows[0].split(',').map(h => h.trim().toLowerCase())
      for (let i = 0; i < headers.length; i++) {
        if (!header.includes(headers[i])) {
          error.value = `CSV is missing required column: ${headers[i]}`
          return
        }
      }

      const dataRows = rows.slice(1)

      students.value = [
        {
          name: 'CSV',
          students: dataRows
            .map(line => {
              const cols = line.split(',')
              return `${cols[0].trim()} ${cols[1].trim()} ${cols[2].trim()}`
            })
            .filter(Boolean)
        }
      ]
    }
        

    reader.readAsText(file)

  } else if (isExcel) {
    const reader = new FileReader()
    reader.onload = (evt) => {
      const data = new Uint8Array(evt.target.result)
      const workbook = XLSX.read(data, { type: 'array' })
      const allStudents = []
      const skipped = []

      for (const sheetName of workbook.SheetNames) {
        const sheetData = []
        const ws = workbook.Sheets[sheetName]
        const json = XLSX.utils.sheet_to_json(ws, { defval: '' }) 
        if (!json || json.length === 0) {
          skipped.push(sheetName)
          continue
        }

        const keys = Object.keys(json[0]).map(k => k.trim().toLowerCase())
        if (!headers.every(h => keys.includes(h))) {
          skipped.push(sheetName)
          continue
        }

        const getCell = (row, key) => {
          const foundKey = Object.keys(row).find(k => k.trim().toLowerCase() === key)
          return foundKey ? row[foundKey] : ''
        }

        for (const row of json) {
          const lastname = String(getCell(row, 'lastname')).trim()
          const firstname = String(getCell(row, 'firstname')).trim()
          const osis = String(getCell(row, 'osis')).trim()
          if (!lastname && !firstname && !osis) continue
          sheetData.push(`${lastname} ${firstname} ${osis}`.trim())
        }

        if (sheetData.length) {
          allStudents.push({
            name: sheetName,
            students: sheetData
          })
        }
      }
      
      if (allStudents.length === 0) {
        error.value = `No valid sheets found. Skipped sheets: ${skipped.join(', ') || 'none'}`
        return
      }

      if (skipped.length) {
        error.value = `Some sheets were skipped (missing headers): ${skipped.join(', ')}`
      }

      students.value = allStudents
    }
    reader.readAsArrayBuffer(file)
  } else {
    error.value = 'Unsupported file type. Please upload a .csv, .xlsx, or .xls file.'
  }
}

function makeGroups() {
  if (!students.value.length) return

  groups.value = [] // clear

  for (const sheet of students.value) {
    // sheet.students is the array for this sheet
    const sheetStudents = sheet.students

    let groupAmount = 0
    if (useMin.value) groupAmount = Math.floor(sheetStudents.length / inputValue.value)
    if (useMax.value) groupAmount = Math.ceil(sheetStudents.length / inputValue.value)
    if (useNumGroups.value) groupAmount = inputValue.value
    if (groupAmount < 1) groupAmount = 1

    const sheetGroups = Array.from({ length: groupAmount }, () => [])

    const shuffle = [...sheetStudents].sort(() => Math.random() - 0.5)
    let groupIndex = 0

    for (const student of shuffle) {
      sheetGroups[groupIndex].push(student)
      groupIndex = (groupIndex + 1) % groupAmount
    }

    groups.value.push({
      name: sheet.name,
      groups: sheetGroups
    })
  }
}


function ChangeBooleans() {
  const val = selectedOption.value
  if (val === 'numGroups') {
    useNumGroups.value = true
    useMin.value = false
    useMax.value = false
  } else if (val === 'min') {
    useNumGroups.value = false
    useMin.value = true
    useMax.value = false
  } else if (val === 'max') {
    useNumGroups.value = false
    useMin.value = false
    useMax.value = true
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
