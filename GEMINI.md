# GEMINI.md

## Architecture
My architecture plan for this project is to implement Client-Server Architecture with MVC.
Here's the explaination for the architecture:
The StudyAid learning management system employs a Client-Server Architecture integrated with the Model-View-Controller (MVC) pattern to ensure a well-structured, maintainable, and scalable application. The client side consists of a web browser that provides the interface for users to interact with the system. It sends requests to the server, which processes these requests, executes business logic, manages data, and returns appropriate responses for rendering in the browser.

The MVC pattern on the server organizes the system into three main components. The Model layer is responsible for managing application data, rules, and business logic. In StudyAid, this includes components such as the AuthModel, LmModel, and UserModel. These models interact with the database to handle storage, retrieval, and updates. The View layer forms the presentation component, including AuthView, LearningView, DashboardView, and ProfileView, which define how information is presented to the user. The Controller layer, represented by AuthController, UserController, and LmController, manages the flow of data between the Model and the View. It processes user requests from the client, updates the appropriate Models, and selects Views to display the results.

The integration of Client-Server and MVC architectures ensures that responsibilities are clearly separated. The client focuses solely on displaying information and capturing user input, while the server handles all business logic and data management. This separation improves system maintainability, as changes in one layer do not directly affect others. It also enhances scalability, since additional features or components can be introduced without disrupting the entire system.

Furthermore, the architecture provides security by centralizing logic and validation on the server, reducing risks from client-side manipulation. Performance is optimized as requests are processed centrally and data interactions are efficiently managed through the database. Overall, this architecture supports reliability, flexibility, and long-term extensibility of the StudyAid learning management system.


## Functional Requirements
CM -  Content Management
CM1_03: The system shall use OCR to extract text from images that contains text and screenshots of text contents.
CM2_04: The system shall organize learning materials by date, or user-defined categories. 

U - User Management Module
U1_03: The system shall send a confirmation message upon successful registration.

LM - Learning Material Management Module
LM4 - Chatbot
LM4_01: The system shall allow the user to submit the question to the chatbot.
LM4_02: The system shall send the submitted question to the AI service via an API request.
LM4_03: The system shall receive the AI-generated response from the API and display it in the user interface.
LM4_04: The system shall log user queries.

LM5 - Quiz
LM5_01: The system shall allow the user to request the generation of a quiz from an uploaded document.
LM5_02: The system shall send the uploaded document to the AI service via an API request for quiz generation.
LM5_03: The system shall receive the AI-generated quiz from the API and display it in a structured quiz interface template.
LM5_04: The system shall allow the user to answer the quiz questions via the user interface.
LM5_05: The system shall evaluate the user’s answers and display the quiz score upon submission.
LM5_06: The system shall store the quiz result in the user's performance record for progress tracking.

LM6 - Flashcard
LM6_01: The system shall allow the user to request the generation of flashcards based on the uploaded document.
LM6_02: The system shall send the uploaded document to the AI service via an API request to extract key terms and definitions.
LM6_03: The system shall receive the AI-generated key terms and definitions from the API and display them in a structured flashcards interface.
LM6_04: The system shall allow the user to flip each flashcard to view both the key term and its corresponding explanation.
LM6_05: The system shall allow the user to navigate through the flashcards.

U - User Management Module
U3 - Password reset
U3_01: The system shall provide a "Forgot Password" option on the login page.
U3_02: The system shall allow users to reset their password by sending a reset link to their registered email.
U3_03: The system shall validate the reset link and allow the user to set a new password.

U4 - Manage profile
U4_01: The system shall allow users to view their personal profile information.
U4_02: The system shall allow users to update their personal profile information.
U4_03: The system shall validate input data when updating the profile.
U4_04: The system shall allow users to change their password from within the profile settings.
U4_05: The system shall display a success message when profile updates are saved.

P - Performance Analysis Module
P1 - Progress report
P1_01: The system shall track each user's learning activities.
P1_02: The system shall generate a progress report based on user interactions and activity history.
P1_03: The system shall allow users to view their progress reports in a clean, readable layout with date and user identification.
P1_04: The system shall allow users to export their progress report in PDF or Excel format.

P2 - Performance dashboard
P2_01: The system shall display key performance  in visual formats (charts/graphs).
P2_02: The system shall update the performance dashboard in real time as the user completes tasks or assessments.
P2_03: The system shall provide filters for users to view performance data over specific time periods (e.g., weekly, monthly).

## Behavior Expectation
You are an expert in coding. You will use the most effective and efficient coding method known. 
No uncessary structures, use the most simplistic and comprehensive coding stucture and manner.
Always review the file and folder first, before modifying the code.

## Programming Language
PHP for backend
Bootstrap 5 for interface
