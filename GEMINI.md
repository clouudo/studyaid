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
CM1 -  Upload Document
CM1_01: The system shall allow the  user to upload learning materials in the format of PDF, Word, Image or text files.
CM1_02: The system shall process learning materials in the format of PDF, Word, Image or text files and convert to machine-readable text.
CM1_03: The system shall use OCR to extract text from images that contains text and screenshots of text contents.

CM2 -  Manage Folders
CM2_01: The system shall allow the user to create folders.
CM2_02: The system shall allow the user to delete existing folders.
CM2_03: The system shall allow the user to edit existing folder names.
CM2_04: The system shall organize learning materials by date, or user-defined categories. 
CM2_05: The system shall enable the user to search and retrieve specific folder.

CM3 -  Manage File
CM3_01: The system shall allow the user to add new files from uploaded documents.
CM3_02: The system shall allow the user to remove existing files.
CM3_03: The system shall allow the user to edit existing file names.
CM3_04: The system shall enable the user to search and retrieve specific files.

LM - Learning Material Management
LM1 -  Summary
LM1_01: The system shall summarize the learning materials selected by the user.
LM1_02: The system shall store generated summary for future access.
LM1_03: The system shall allow users to export summary in the format of PDF, Word or text files.

LM2 -  Note
LM2_01: The system shall generate notes based on uploaded learning materials selected by the user.
LM2_02: The system shall store generated notes for future access.
LM2_03: The system shall allow users to export notes in the format of PDF, Word or text files.

LM3 - Mind map
LM3_01: The system shall generate mind maps based on uploaded learning material selected by the user.
LM3_02: The system shall store generated mind map for future access.
LM3_03: The system shall allow users to export mind maps in the format of PDF or image files.

U - User Management Module
U1 - Register
U1_01: The system shall provide a registration form to allow new users to create an account using a valid email and password.
U1_02: The system shall validate that email address is not used by other accounts during registration.
U1_03: The system shall send a confirmation message upon successful registration.

U2 - Login
U2_01: The system shall allow users to log in using their registered email and password.
U2_02: The system shall validate the login credentials before granting access to the system.
U2_03: The system shall display an appropriate error message if login credentials are invalid.
U2_04: The system shall maintain a user session upon successful login.

## Behavior Expectation
You are an expert in coding. You will use the most effective and efficient coding method known. 
No uncessary structures, use the most simplistic and comprehensive coding stucture and manner.
Always review the code first, before modifying the code.

## Programming Language
PHP for backend
Bootstrap 4 for interface
