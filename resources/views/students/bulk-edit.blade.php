<!DOCTYPE html>
<html>
<head>
    <title>Bulk Edit Students</title>
</head>
<body>

    <h1>Bulk Edit Students</h1>

    <form action="{{ route('students.bulkUpdate') }}" method="POST">

        @csrf
        @method('PUT')

        @foreach($students as $student)

            <div>
                <h3>Student ID: {{ $student->id }}</h3>

                <label>Name:</label>
                <input
                    type="text"
                    name="students[{{ $student->id }}][name]"
                    value="{{ $student->name }}"
                >

                <br><br>

                <label>Email:</label>
                <input
                    type="email"
                    name="students[{{ $student->id }}][email]"
                    value="{{ $student->email }}"
                >

                <br><br>

                <label>Phone:</label>
                <input
                    type="text"
                    name="students[{{ $student->id }}][phone]"
                    value="{{ $student->phone }}"
                >

                <hr>

            </div>

        @endforeach

        <button type="submit">
            Update Selected Students
        </button>

    </form>

</body>
</html>