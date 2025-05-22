import React, { useEffect, useState } from 'react';
import { Users, Bed, Clock, Activity } from 'lucide-react';
import { Patient, Room } from '../types';
import { fetchPatients, fetchRooms } from '../utils/api';
import StatCard from '../components/Dashboard/StatCard';
import RecentPatients from '../components/Dashboard/RecentPatients';
import RoomOccupancy from '../components/Dashboard/RoomOccupancy';

const Dashboard: React.FC = () => {
  const [patients, setPatients] = useState<Patient[]>([]);
  const [rooms, setRooms] = useState<Room[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  
  useEffect(() => {
    const loadData = async () => {
      try {
        const [patientsData, roomsData] = await Promise.all([
          fetchPatients(),
          fetchRooms()
        ]);
        
        setPatients(patientsData);
        setRooms(roomsData);
      } catch (error) {
        console.error('Error loading dashboard data:', error);
      } finally {
        setLoading(false);
      }
    };
    
    loadData();
  }, []);
  
  // Calculate dashboard statistics
  const admittedPatients = patients.filter(p => p.status === 'admitted').length;
  const dischargedPatients = patients.filter(p => p.status === 'discharged').length;
  
  const totalBeds = rooms.reduce((acc, room) => acc + room.beds.length, 0);
  const occupiedBeds = rooms.reduce(
    (acc, room) => acc + room.beds.filter(bed => bed.status === 'occupied').length, 
    0
  );
  const availableBeds = totalBeds - occupiedBeds;
  
  // Get recent patients (admitted only, sorted by admission date)
  const recentPatients = patients
    .filter(p => p.status === 'admitted')
    .sort((a, b) => new Date(b.admissionDate).getTime() - new Date(a.admissionDate).getTime())
    .slice(0, 5);
  
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
        <h1 className="text-2xl font-bold text-gray-800">Dashboard</h1>
        <p className="text-gray-500">Overview of hospital inpatient management</p>
      </div>
      
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard 
          title="Current Patients" 
          value={admittedPatients} 
          icon={<Users size={20} />}
          change={{ value: 12, type: 'increase' }}
          color="blue"
        />
        <StatCard 
          title="Available Beds" 
          value={availableBeds} 
          icon={<Bed size={20} />}
          change={{ value: 5, type: 'decrease' }}
          color="green"
        />
        <StatCard 
          title="Average Stay" 
          value="4.2 days" 
          icon={<Clock size={20} />}
          color="orange"
        />
        <StatCard 
          title="Discharged (Month)" 
          value={dischargedPatients} 
          icon={<Activity size={20} />}
          change={{ value: 8, type: 'increase' }}
          color="purple"
        />
      </div>
      
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2">
          <RecentPatients patients={recentPatients} />
        </div>
        <div>
          <RoomOccupancy rooms={rooms} />
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
