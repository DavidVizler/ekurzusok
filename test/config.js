// Weboldal URL címe
const URL = 'http://localhost/13c-vizler/ekurzusok';

// Teszt fiók email címe
const EMAIL = 'teszt@teszt.com';

// Teszt fiók jelszava
const PASSWORD = 'Teszt4321';

module.exports = {
    URL,
    EMAIL,
    PASSWORD,
    SIGNUP_URL:             URL + '/signup.html',
    LOGIN_URL:              URL + '/login.html',
    COURSES_URL:            URL + '/kurzusok.html',
    USER_URL:               URL + '/userPage.html',
    CREATE_COURSE_URL:      URL + '/kurzusAdd.html',
    COURSE_URL:             URL + '/kurzus/',
    COURSE_MEMBERS_URL:     URL + '/kurzus/users.html?id=',
    COURSE_ADD_CONTENT_URL: URL + '/kurzus/addContent.html?id=',
    TASK_URL:               URL + '/feladat.html?id=',
    SUBMISSIONS_URL:        URL + '/submissions.html?id='
}