// api.services.js
import { api } from "./axios";
// Function 1
async function getNotes() {
   try {
   const response = await api.get("/api/notes");
    return response.data;
   } catch (error) {

   }
}

// Function 2
async function getSingleNotes(id) {
  try {
    const response = await api.get("/api/notes/"+id);
     return response.data;
    } catch (error) {

    }
}
// Exporting functions
export default {
  getNotes,
  getSingleNotes
};
