import { Routes, Route } from "react-router-dom";
import RegisterForm from "./components/RegisterForm";
import PaidList from "./components/PaidList";
import EditForm from "./components/EditForm";

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<RegisterForm />} />
      <Route path="/lista" element={<PaidList />} />
      <Route path="/muokkaa" element={<EditForm />} />
    </Routes>
  );
}
