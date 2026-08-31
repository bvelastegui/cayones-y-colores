import { ref } from 'vue';

export interface CurrentStudent {
    id: number;
    first_name: string;
    last_name: string;
    course_name: string | null;
}

const selectedStudent = ref<CurrentStudent | null>(null);

export function useCurrentStudent() {
    function setCurrentStudent(student: CurrentStudent | null): void {
        selectedStudent.value = student;
    }

    return {
        selectedStudent,
        setCurrentStudent,
    };
}
