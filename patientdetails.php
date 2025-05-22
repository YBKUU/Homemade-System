import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { Patient } from '../types';
import { fetchPatientById } from '../utils/api';
import PatientDetail from '../components/Patients/PatientDetail';

const PatientDetails: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const [patient, setPatient] = useState<Patient | null>(null);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);
  
  useEffect(() => {
    const loadPatient = async () => {
      try {
        if (!id) {
          setError('Patient ID is missing');
          setLoading(false);
          return;
        }
        
        const data = await fetchPatientById(id);
        
        if (!data) {
          setError('Patient not found');
        } else {
          setPatient(data);
          setError(null);
        }
      } catch (err) {
        console.error('Error loading patient details:', err);
        setError('Failed to load patient details');
      } finally {
        setLoading(false);
      }
    };
    
    loadPatient();
  }, [id]);
  
  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }
  
  if (error || !patient) {
    return (
      <div className="bg-white rounded-lg shadow-sm p-6 text-center">
        <h2 className="text-xl font-medium text-gray-800 mb-2">Error</h2>
        <p className="text-gray-600">{error || 'Failed to load patient'}</p>
      </div>
    );
  }
  
  return <PatientDetail patient={patient} />;
};

export default PatientDetails;
