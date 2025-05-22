import React, { useEffect, useState } from 'react';
import { Room } from '../types';
import { fetchRooms } from '../utils/api';
import RoomList from '../components/Rooms/RoomList';

const Rooms: React.FC = () => {
  const [rooms, setRooms] = useState<Room[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  
  useEffect(() => {
    const loadRooms = async () => {
      try {
        const data = await fetchRooms();
        setRooms(data);
      } catch (error) {
        console.error('Error loading rooms:', error);
      } finally {
        setLoading(false);
      }
    };
    
    loadRooms();
  }, []);
  
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
        <h1 className="text-2xl font-bold text-gray-800">Room Management</h1>
        <p className="text-gray-500">View and manage hospital rooms and beds</p>
      </div>
      
      <RoomList rooms={rooms} />
    </div>
  );
};

export default Rooms;
