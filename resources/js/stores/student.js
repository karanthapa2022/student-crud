import { defineStore } from 'pinia'
import { getStudents, createStudent } from '../services/studentApi'
import router from '../router'

export const useStudentStore = defineStore('student', {

    state: () => ({
        students: [],
        loading: false,
        error: null
    }), 
    actions:{
        //====================
        //Fetch Students
        //====================
        async fetchStudents(){
            this.loading=true
            this.error=null
            try{
                const response =await getStudents()
                this.students= response.data
            }
            catch(error){
                console.error('Error fetching students:',error)
                if(error.response?.status===401){
                    localStorage.removeItem('token')
                    localStorage.removeItem('user')
                    router.push('/login')
                    return
                }
                this.error='Unable to load students.'
            } finally{
                this.loading=false
            }
        },
        //====================
    //Add students
    //====================
        async addStudent(studentData){
            this.error=null
            try{
                const response=
                await createStudent(studentData)
                console.log('Add student response:', response.data)
                const student=
                response.data.student||
                response.data
                console.log('student added to pinia:', student)
                this.students.unshift(student)
                return student
            }
            catch (error){
                console.error('Error adding student:',error)
                this.error= error.response?.data?.message||'Error adding student.'
                throw error
            }
        }

    }
   
})