import { jsPDF } from 'jspdf'

export const downloadMarksheetPdf = (marksheet) => {

    

    const doc = new jsPDF()

    // =========================================================
    // PAGE BORDER
    // =========================================================

    doc.setLineWidth(0.5)

    doc.rect(
        10,
        10,
        190,
        277
    )


    // =========================================================
    // HEADER
    // =========================================================

    doc.setFont('helvetica', 'bold')
    doc.setFontSize(16)

    doc.text(
        'Secondary Education Examination',
        105,
        22,
        {
            align: 'center'
        }
    )

    doc.setFontSize(14)

    doc.text(
        'GRADE-SHEET',
        105,
        31,
        {
            align: 'center'
        }
    )


    doc.setFont('helvetica', 'normal')
    doc.setFontSize(9)

    doc.text(
        'Academic Performance Record',
        105,
        38,
        {
            align: 'center'
        }
    )


    // =========================================================
    // STUDENT INFORMATION
    // =========================================================

    doc.setLineWidth(0.3)

    doc.rect(
        20,
        45,
        170,
        32
    )

    doc.setFontSize(9)

    doc.text(
        'THE GRADE(S) SECURED BY',
        24,
        52
    )

    doc.setFont('helvetica', 'bold')

    doc.text(
        marksheet.student?.name || '-',
        72,
        52
    )

    doc.setFont('helvetica', 'normal')

    doc.text(
        'SYMBOL NO.',
        24,
        61
    )

    doc.setFont('helvetica', 'bold')

    doc.text(
        marksheet.student?.symbol_no || '-',
        58,
        61
    )

    doc.setFont('helvetica', 'normal')

    doc.text(
        'CLASS',
        120,
        61
    )

    doc.setFont('helvetica', 'bold')

    doc.text(
        marksheet.student?.class || '-',
        145,
        61
    )

    doc.setFont('helvetica', 'normal')

    doc.text(
        'EXAMINATION',
        24,
        70
    )

    doc.text(
        'Annual Examination',
        60,
        70
    )


    // =========================================================
    // TABLE HEADER
    // =========================================================

    let y = 88

    const tableX = 20
    const tableWidth = 170

    const colSN = 10
    const colSubject = 65
    const colTH = 20
    const colPR = 20
    const colGrade = 18
    const colPoint = 22
    const colCredit = 15

    const rowHeight = 10

    doc.setFont('helvetica', 'bold')
    doc.setFontSize(8)

    // Header cells

    doc.rect(
        tableX,
        y,
        colSN,
        rowHeight
    )

    doc.rect(
        tableX + colSN,
        y,
        colSubject,
        rowHeight
    )

    doc.rect(
        tableX + colSN + colSubject,
        y,
        colTH,
        rowHeight
    )

    doc.rect(
        tableX + colSN + colSubject + colTH,
        y,
        colPR,
        rowHeight
    )

    doc.rect(
        tableX + colSN + colSubject + colTH + colPR,
        y,
        colGrade,
        rowHeight
    )

    doc.rect(
        tableX + colSN + colSubject + colTH + colPR + colGrade,
        y,
        colPoint,
        rowHeight
    )

    doc.rect(
        tableX + colSN + colSubject + colTH + colPR + colGrade + colPoint,
        y,
        colCredit,
        rowHeight
    )

    doc.text(
        'S.N.',
        tableX + 5,
        y + 6,
        { align: 'center' }
    )

    doc.text(
        'SUBJECTS',
        tableX + colSN + 3,
        y + 6
    )

    doc.text(
        'TH',
        tableX + colSN + colSubject + 10,
        y + 6,
        { align: 'center' }
    )

    doc.text(
        'PR',
        tableX + colSN + colSubject + colTH + 10,
        y + 6,
        { align: 'center' }
    )

    doc.text(
        'GRADE',
        tableX + colSN + colSubject + colTH + colPR + 9,
        y + 6,
        { align: 'center' }
    )

    doc.text(
        'GRADE',
        tableX + colSN + colSubject + colTH + colPR + colGrade + 11,
        y + 4,
        { align: 'center' }
    )

    doc.text(
        'POINT',
        tableX + colSN + colSubject + colTH + colPR + colGrade + 11,
        y + 8,
        { align: 'center' }
    )

    doc.text(
        'CREDIT',
        tableX + colSN + colSubject + colTH + colPR + colGrade + colPoint + 7,
        y + 6,
        { align: 'center' }
    )


    // =========================================================
    // SUBJECT ROWS
    // =========================================================

    y += rowHeight

    doc.setFont('helvetica', 'normal')
    doc.setFontSize(8)

    marksheet.items.forEach((item, index) => {

        const rawMarks = String(item.marks || '')
            .trim()
            .toUpperCase()

        let grade = 'F'
        let gradePoint = 0

        if (rawMarks === 'A') {

            grade = 'A*'
            gradePoint = 0

        } else {

            const value = Number(rawMarks)

            if (value >= 80) {
                grade = 'A+'
                gradePoint = 4.0
            } else if (value >= 70) {
                grade = 'A'
                gradePoint = 3.6
            } else if (value >= 60) {
                grade = 'B+'
                gradePoint = 3.2
            } else if (value >= 50) {
                grade = 'B'
                gradePoint = 2.8
            } else if (value >= 40) {
                grade = 'C'
                gradePoint = 2.4
            } else if (value >= 30) {
                grade = 'D'
                gradePoint = 1.6
            } else {
                grade = 'F'
                gradePoint = 0
            }

        }

        // Draw row

        doc.rect(
            tableX,
            y,
            colSN,
            rowHeight
        )

        doc.rect(
            tableX + colSN,
            y,
            colSubject,
            rowHeight
        )

        doc.rect(
            tableX + colSN + colSubject,
            y,
            colTH,
            rowHeight
        )

        doc.rect(
            tableX + colSN + colSubject + colTH,
            y,
            colPR,
            rowHeight
        )

        doc.rect(
            tableX + colSN + colSubject + colTH + colPR,
            y,
            colGrade,
            rowHeight
        )

        doc.rect(
            tableX + colSN + colSubject + colTH + colPR + colGrade,
            y,
            colPoint,
            rowHeight
        )

        doc.rect(
            tableX + colSN + colSubject + colTH + colPR + colGrade + colPoint,
            y,
            colCredit,
            rowHeight
        )


        // S.N.

        doc.text(
            String(index + 1),
            tableX + colSN / 2,
            y + 6,
            { align: 'center' }
        )


        // Subject

        doc.text(
            item.subject?.name || '-',
            tableX + colSN + 3,
            y + 6
        )


        // Theory marks

        doc.text(
            rawMarks === 'A' ? 'A' : rawMarks,
            tableX + colSN + colSubject + 10,
            y + 6,
            { align: 'center' }
        )


        // Practical

        doc.text(
            '-',
            tableX + colSN + colSubject + colTH + 10,
            y + 6,
            { align: 'center' }
        )


        // Grade

        doc.text(
            grade,
            tableX + colSN + colSubject + colTH + colPR + 9,
            y + 6,
            { align: 'center' }
        )


        // Grade point

        doc.text(
            gradePoint.toFixed(1),
            tableX + colSN + colSubject + colTH + colPR + colGrade + 11,
            y + 6,
            { align: 'center' }
        )


        // Credit

        doc.text(
            '1',
            tableX + colSN + colSubject + colTH + colPR + colGrade + colPoint + 7,
            y + 6,
            { align: 'center' }
        )


        y += rowHeight

    })


    // =========================================================
    // GPA / RESULT SECTION
    // =========================================================

    y += 6

    doc.setFont('helvetica', 'bold')
    doc.setFontSize(10)

    doc.rect(
        20,
        y,
        170,
        12
    )

    doc.text(
        `TOTAL MARKS: ${marksheet.total}`,
        25,
        y + 8
    )

    doc.text(
        `PERCENTAGE: ${Number(marksheet.percentage).toFixed(2)}%`,
        85,
        y + 8
    )

    doc.text(
        `RESULT: ${marksheet.result || '-'}`,
        155,
        y + 8,
        { align: 'center' }
    )


    y += 20

    doc.rect(
        20,
        y,
        170,
        14
    )

    doc.setFontSize(11)

    const gradePoints = marksheet.items.map(item => {

    const value = String(item.marks || '')
        .trim()
        .toUpperCase()

    if (value === 'A') {
        return 0
    }

    const marks = Number(value)

    if (marks >= 80) {
        return 4.0
    } else if (marks >= 70) {
        return 3.6
    } else if (marks >= 60) {
        return 3.2
    } else if (marks >= 50) {
        return 2.8
    } else if (marks >= 40) {
        return 2.4
    } else if (marks >= 30) {
        return 1.6
    }

    return 0
})

const gpa = gradePoints.length > 0
    ? gradePoints.reduce((sum, point) => sum + point, 0) / gradePoints.length
    : 0

doc.text(
    `GRADE POINT AVERAGE (GPA): ${gpa.toFixed(2)}`,
    25,
    y + 9
)


    // =========================================================
    // NOTES
    // =========================================================

    y += 24

    doc.setFont('helvetica', 'bold')
    doc.setFontSize(9)

    doc.text(
        'Notes:',
        20,
        y
    )

    doc.setFont('helvetica', 'normal')
    doc.setFontSize(8)

    y += 7

    doc.text(
        '1. TH: Theory, PR: Practical.',
        20,
        y
    )

    y += 6

    doc.text(
        '2. A: Absent.',
        20,
        y
    )

    y += 6

    doc.text(
        '3. A student obtaining less than 40 marks in any subject is considered Fail.',
        20,
        y
    )


    // =========================================================
    // PRINCIPAL SIGNATURE
    // =========================================================

    doc.line(
        145,
        250,
        180,
        250
    )

    doc.setFontSize(8)

    doc.text(
        'Principal / Head Teacher',
        162.5,
        256,
        {
            align: 'center'
        }
    )


    // =========================================================
    // FOOTER
    // =========================================================

    doc.setFontSize(7)

    doc.text(
        'This sheet is for general information only and is not an official government document.',
        105,
        280,
        {
            align: 'center'
        }
    )


    // =========================================================
    // SAVE PDF
    // =========================================================

    doc.save(
        `${marksheet.student?.name || 'student'}-grade-sheet.pdf`
    )

}

