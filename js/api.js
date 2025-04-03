// Használat (példa): let [response, result] = await API.getUserCourses();
// response: a kérés adatai (pl.: státuszkód)
// result: JSON formátumú adat, a kérés erdménye

class API {
    static async fetch(url, data = null) {
        let response;
        if (data == null) {
            response = await fetch(url);
        }
        else {
            response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
        }
        let result = await response.json();
        return [response, result];
    }

    static async login(email, password) {
        return await this.fetch('api/user/login', {
            email,
            password
        });
    }

    static async signup(email, firstname, lastname, password) {
        return await this.fetch('api/user/signup', {
            email,
            firstname,
            lastname,
            password
        });
    }

    static async modifyUserData(email, firstname, lastname, password) {
        return await this.fetch('api/user/modify-data', {
            email,
            firstname,
            lastname,
            password
        });
    }

    static async userChangePassword(old_password, new_password) {
        return await this.fetch('api/user/change-password', {
            old_password,
            new_password
        });
    }

    static async deleteUser(password) {
        return await this.fetch('api/user/delete', { password });
    }

    static async createCourse(name, desc, design) {
        return await this.fetch('api/course/create', {
            name,
            desc,
            design
        });
    }

    static async courseModifyData(id, name, desc, design) {
        return await this.fetch('api/course/modify-data', {
            id,
            name,
            desc,
            design
        });
    }

    static async deleteCourse(id) {
        return await this.fetch('api/course/delete', { id });
    }

    static async archiveCourse(id) {
        return await this.fetch('api/course/archive', { id });
    }

    static async joinCourse(code) {
        return await this.fetch('api/member/add', { code });
    }

    static async kickMember(user_id, course_id) {
        return await this.fetch('api/member/remove', {
            user_id,
            course_id
        });
    }

    static async leaveCourse(course_id) {
        return await this.fetch('api/member/leave', { course_id });
    }

    static async makeTeacher(user_id, course_id) {
        return await this.fetch('api/member/teacher', {
            user_id,
            course_id
        });
    }

    // TODO
    // static async createContent(courseId, title, desc, task, maxpoint, deadline, files) {
    //     return await this.fetch('api/content/create', {

    //     })
    // }

    static async publishContent(content_id) {
        return await this.fetch('api/content/publish', { content_id });
    }

    // TODO: upload files

    static async contentRemoveFile(content_id, file_id) {
        return await this.fetch('api/content/remove-file', {
            content_id,
            file_id
        });
    }

    static async contentModifyData(content_id, title, desc, task, maxpoint, deadline) {
        return await this.fetch('api/content/modify-data', {
            content_id,
            title,
            desc,
            task,
            maxpoint,
            deadline
        });
    }

    static async deleteContent(content_id) {
        return await this.fetch('api/content/delete', { content_id });
    }

    // TODO: submission upload file

    static async submissionRemoveFile(content_id, file_id) {
        return await this.fetch('api/submission/remove-file', {
            content_id,
            file_id
        });
    }

    static async submitSubmission(content_id) {
        return await this.fetch('api/submission/submit', { content_id });
    }

    static async rateSubmission(content_id, points) {
        return await this.fetch('api/submission/rate', {
            content_id,
            points
        });
    }

    static async getCourseMembers(course_id) {
        return await this.fetch('api/query/course-members', { course_id });
    }

    static async getUserCourses() {
        return await this.fetch('api/query/user-courses');
    }

    static async getCourseData(course_id) {
        return await this.fetch('api/query/course-data', { course_id });
    }

    static async getUserData() {
        return await this.fetch('api/query/user-data');
    }

    static async getCourseContent(course_id) {
        return await this.fetch('api/query/course-content', { course_id });
    }

    static async getContentData(content_id) {
        return await this.fetch('api/query/content-data', { content_id });
    }

    static async getDeadlineTasks() {
        return await this.fetch('api/query/deadline-tasks');
    }

    static async getContentFiles(content_id) {
        return await this.fetch('api/query/content-files', { content_id });
    }

    static async getOwnSubmission(content_id) {
        return await this.fetch('api/query/own-submission', { content_id });
    }

    static async getSubmissions(content_id) {
        return await this.fetch('api/query/submissions', { content_id });
    }

    static async getSubmissionCount(content_id) {
        return await this.fetch('api/query/submission-count', { content_id });
    }

    static async getSubmissionFiles(submission_id) {
        return await this.fetch('api/query/submission-files', { submission_id });
    }
}

if (typeof module !== 'undefined') {
    module.exports = API;
}