import React, { useEffect, useState } from 'react';
import { Room, Staff } from '../types';
import { fetchRooms, fetchStaff, createPatient } from '../utils/api';
import AdmissionForm, { AdmissionFormData } from '../components/Admissions/AdmissionForm';
import { useNavigate } from 'react-router-dom';

const Admissions: React.FC = () => {
  const [rooms, setRooms] = useState<Room[]>([]);
  const [staff, setStaff] = useState<Staff[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [submitting, setSubmitting] = useState<boolean>(false);
  const navigate = useNavigate();
  
  useEffect(() => {
    const loadData = async () => {
      try {
        const [roomsData, staffData] = await Promise.all([
          fetchRooms(),
          fetchStaff()
        ]);
        
        setRooms(roomsData);
        setStaff(staffData);
      } catch (error) {
        console.error('Error loading admission data:', error);
      } finally {
        setLoading(false);
      }
    };
    
    loadData();
  }, []);
  
  const handleSubmit = async (formData: AdmissionFormData) => {
    try {
      setSubmitting(true);
      
      // Get room and bed information
      const selectedRoom = rooms.find(room => room.id === formData.roomId);
      const selectedBed = selectedRoom?.beds.find(bed => bed.id === formData.bedId);
      
      // Get staff information
      const selectedDoctor = staff.find(s => s.id === formData.doctorId);
      const selectedNurse = staff.find(s => s.id === formData.nurseId);
      
      if (!selectedRoom || !selectedBed || !selectedDoctor || !selectedNurse) {
        console.error('Missing selected data');
        return;
      }
      
      // Create patient object from form data
      const patientData = {
        name: formData.name,
        age: formData.age,
        gender: formData.gender,
        bloodType: formData.bloodType,
        contactNumber: formData.contactNumber,
        emergencyContact: formData.emergencyContact,
        address: formData.address,
        admissionDate: new Date().toISOString().split('T')[0],
        status: 'admitted' as const,
        roomNumber: selectedRoom.roomNumber,
        bedNumber: selectedBed.bedNumber,
        diagnosis: formData.diagnosis,
        notes: formData.notes,
        assignedDoctor: selectedDoctor.name,
        assignedNurse: selectedNurse.name,
        medications: [],
        treatments: []
      };
      
      // Create the patient
      const newPatient = await createPatient(patientData);
      
      // Navigate to the patient details page
      navigate(`/patients/${newPatient.id}`);
      
    } catch (error) {
      console.error('Error submitting admission:', error);
    } finally {
      setSubmitting(false);
    }
  };
  
  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }
  
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-800">New Admission</h1>
        <p className="text-gray-500">Register and admit a new patient</p>
      </div>
      
      <AdmissionForm 
        rooms={rooms} 
        doctors={staff.filter(s => s.role === 'doctor')} 
        nurses={staff.filter(s => s.role === 'nurse')} 
        onSubmit={handleSubmit} 
      />
      
      {submitting && (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white p-6 rounded-lg shadow-lg">
            <div className="flex items-center space-x-4">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              <p className="text-gray-700">Processing admission...</p>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default Admissions;
